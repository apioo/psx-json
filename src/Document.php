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

namespace PSX\Json;

use InvalidArgumentException;
use PSX\Record\Record;

/**
 * Document
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Document extends Record
{
    /**
     * Resolves the provided json pointer to an element in the document or 
     * returns null if the element does not exist
     */
    public function get(string $pointer): mixed
    {
        $pointer = new Pointer($pointer);
        $data    = $pointer->evaluate($this->_properties);

        return $data;
    }

    /**
     * Checks whether this document is equal to the provided document
     */
    public function equals(mixed $document): bool
    {
        return Comparator::compare($this, $document);
    }

    /**
     * Executes patch operations on this document
     */
    public function patch(array $operations)
    {
        $patch = new Patch($operations);
        $data  = $patch->patch($this->_properties);

        $this->_properties = $data;
    }

    /**
     * Returns this document as json encoded string
     */
    public function toString(): string
    {
        return Parser::encode($this->_properties);
    }

    public static function fromFile(string $file): Document
    {
        return self::fromJson(file_get_contents($file));
    }

    public static function fromJson(string $json): Document
    {
        if (empty($json)) {
            throw new InvalidArgumentException('Provided JSON string must not be empty');
        }

        return self::fromStdClass(Parser::decode($json));
    }
}
