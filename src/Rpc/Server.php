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

namespace PSX\Json\Rpc;

use PSX\Json\Rpc\Exception\InvalidRequestException;

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
    private \Closure $callable;
    private bool $debug;
    private Builder $builder;

    public function __construct(\Closure $callable, bool $debug = false)
    {
        $this->callable = $callable;
        $this->debug = $debug;
        $this->builder = new Builder();
    }

    public function invoke($data): object|array
    {
        if (is_array($data)) {
            if (count($data) === 0) {
                return $this->builder->createError(new InvalidRequestException('Invalid Request'), null);
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

    private function execute($data): object
    {
        $method = $data->method ?? null;
        $params = $data->params ?? null;
        $id = $data->id ?? null;

        if (empty($method)) {
            return $this->builder->createError(new InvalidRequestException('Invalid Request'), $id);
        }

        try {
            return $this->builder->createResponse(call_user_func_array($this->callable, [$method, $params]), $id);
        } catch (\Throwable $e) {
            return $this->builder->createError($e, $id, $this->debug);
        }
    }
}
