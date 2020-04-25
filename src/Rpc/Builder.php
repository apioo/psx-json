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

/**
 * Builder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Builder
{
    private const VERSION = '2.0';

    public function createCall(string $method, $arguments, int $id = null)
    {
        return (object) [
            'jsonrpc' => self::VERSION,
            'method' => $method,
            'params' => $arguments,
            'id' => $id,
        ];
    }

    public function createResponse($result, int $id = null)
    {
        return (object) [
            'jsonrpc' => self::VERSION,
            'result' => $result,
            'id' => $id,
        ];
    }

    public function createError(\Throwable $e, int $id = null, bool $debug = false)
    {
        $error = (object) [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];

        if ($debug) {
            $error->data = $e->getTraceAsString();
        }

        return (object) [
            'jsonrpc' => self::VERSION,
            'error' => $error,
            'id' => $id,
        ];
    }
}
