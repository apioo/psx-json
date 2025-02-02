<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

/**
 * This class is a wrapper to the json_encode / json_decode functions. Here a simple example howto use it.
 * <code>
 * $json = Parser::encode(['foo' => 'bar']);
 * $php  = Parser::decode($json);
 * </code>
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Parser
{
    /**
     * Returns the json encoded value as string of $value
     *
     * @throws \JsonException
     */
    public static function encode(mixed $value, ?int $options = null): string
    {
        $return = json_encode($value, $options ?? JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($return === false) {
            throw new \JsonException('Could not encode JSON');
        }

        return $return;
    }

    /**
     * Returns a php variable from the json decoded value. Throws an exception
     * if decoding the data is not possible
     *
     * @throws \JsonException
     */
    public static function decode(string $value, bool $assoc = false): mixed
    {
        $return = json_decode($value, $assoc, 512, JSON_THROW_ON_ERROR);
        if ($return === false) {
            throw new \JsonException('Could not decode JSON');
        }

        return $return;
    }

    /**
     * @throws \JsonException
     */
    public static function decodeAsArray(string $value): array
    {
        return self::decode($value, true);
    }

    /**
     * @throws \JsonException
     */
    public static function decodeAsObject(string $value): object
    {
        return self::decode($value, false);
    }
}
