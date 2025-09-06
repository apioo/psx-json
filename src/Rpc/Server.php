<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Closure;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use stdClass;
use Throwable;

/**
 * Simple JSON RPC server which accepts the decoded JSON payload and invokes the
 * provided callable
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Server
{
    private Closure $callable;
    private bool $debug;
    private Builder $builder;

    public function __construct(Closure $callable, bool $debug = false)
    {
        $this->callable = $callable;
        $this->debug = $debug;
        $this->builder = new Builder();
    }

    public function invoke(mixed $data): object|array
    {
        try {
            if (is_array($data)) {
                if (count($data) === 0) {
                    throw new InvalidRequestException('Provided no values for batch request');
                }

                $result = [];
                foreach ($data as $row) {
                    $result[] = $this->execute($row);
                }

                return $result;
            } else if ($data instanceof stdClass) {
                return $this->execute($data);
            } else {
                throw new InvalidRequestException('Provided invalid request data, must be either an object or array for batch requests');
            }
        } catch (Throwable $e) {
            return $this->builder->createError($e, null, $this->debug);
        }
    }

    private function execute(mixed $data): object
    {
        $id = null;

        try {
            if (!$data instanceof stdClass) {
                throw new InvalidRequestException('Provided an invalid payload, must be an object');
            }

            $method = $data->method ?? null;
            $params = $data->params ?? null;
            $id = $data->id ?? null;

            if (!$this->validateMethod($method)) {
                throw new InvalidRequestException('Provided method must be a string');
            }

            if (!$this->validateParams($params)) {
                throw new InvalidRequestException('Provided params must be an array or object');
            }

            if (!$this->validateId($id)) {
                throw new InvalidRequestException('Provided id must be an integer or string');
            }

            $return = call_user_func_array($this->callable, [$method, $params]);

            return $this->builder->createResponse($return, $id);
        } catch (Throwable $e) {
            return $this->builder->createError($e, $id, $this->debug);
        }
    }

    private function validateMethod(mixed $method): bool
    {
        return is_string($method) && $method !== '';
    }

    private function validateParams(mixed $params): bool
    {
        return is_array($params) || $params instanceof stdClass || $params === null;
    }

    private function validateId(mixed $id): bool
    {
        return is_string($id) || is_int($id) || $id === null;
    }
}
