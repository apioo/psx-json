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

namespace PSX\Json;

use PSX\Json\Exception\PointerException;
use PSX\Record\RecordInterface;

/**
 * Pointer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 * @see     https://tools.ietf.org/html/rfc6901
 */
class Pointer
{
    private string $path;
    private array $parts;

    public function __construct(string $path)
    {
        $this->path  = $path;
        $this->parts = $this->parsePointer($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @throws PointerException
     */
    public function evaluate(mixed $data): mixed
    {
        $path = [];
        foreach ($this->parts as $part) {
            if (is_array($data)) {
                if (array_key_exists($part, $data)) {
                    $data = $data[$part];
                } else {
                    throw new PointerException('Property ' . $part . ' does not exist at /' . implode('/', $path));
                }
            } elseif ($data instanceof \stdClass) {
                if (property_exists($data, $part)) {
                    $data = $data->$part;
                } else {
                    throw new PointerException('Property ' . $part . ' does not exist at /' . implode('/', $path));
                }
            } elseif ($data instanceof RecordInterface) {
                if ($data->containsKey($part)) {
                    $data = $data->get($part);
                } else {
                    throw new PointerException('Property ' . $part . ' does not exist at /' . implode('/', $path));
                }
            } else {
                $data = null;
            }

            $path[] = $part;

            if ($data === null) {
                break;
            }
        }

        return $data;
    }

    /**
     * @throws PointerException
     */
    private function parsePointer(string $path): array
    {
        if (empty($path)) {
            return [];
        }

        $path  = rawurldecode($path);
        $parts = explode('/', $path);

        if (array_shift($parts) !== '') {
            throw new PointerException('Pointer must start with a /');
        }

        return array_map(function ($value) {
            return str_replace(['~1', '~0'], ['/', '~'], $value);
        }, $parts);
    }
}
