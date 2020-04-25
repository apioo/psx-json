<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Json\RPC;

use PSX\Json\RPC\Exception\InvalidRequestException;

/**
 * Simple JSON RPC server which accepts the decoded JSON payload and invokes the
 * provided callable
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Server
{
    private const VERSION = '2.0';

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var bool
     */
    private $debug;

    public function __construct($callable, bool $debug = false)
    {
        $this->callable = $callable;
        $this->debug = $debug;
    }

    public function invoke($data)
    {
        if (is_array($data)) {
            if (count($data) === 0) {
                return $this->createError(new InvalidRequestException('Invalid Request'), null);
            }

            $result = [];
            foreach ($data as $row) {
                $result[] = $this->execute($row);
            }

            return $result;
        } else {
            return $this->execute($data);
        }
    }

    private function execute($data)
    {
        $method = $data->method ?? null;
        $params = $data->params ?? null;
        $id = $data->id ?? null;

        if (empty($method)) {
            return $this->createError(new InvalidRequestException('Invalid Request'), $id);
        }

        try {
            return $this->createResponse(call_user_func_array($this->callable, [$method, $params]), $id);
        } catch (\Throwable $e) {
            return $this->createError($e, $id);
        }
    }

    private function createResponse($result, $id)
    {
        return (object) [
            'jsonrpc' => self::VERSION,
            'result' => $result,
            'id' => $id,
        ];
    }

    private function createError(\Throwable $e, $id)
    {
        $error = (object) [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];

        if ($this->debug) {
            $error->data = $e->getTraceAsString();
        }

        return (object) [
            'jsonrpc' => self::VERSION,
            'error' => $error,
            'id' => $id,
        ];
    }
}
