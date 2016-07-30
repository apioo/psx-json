<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use PSX\Record\Record;
use PSX\Record\RecordInterface;
use InvalidArgumentException;

/**
 * Document
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Document extends Record
{
    /**
     * Resolves the provided json pointer to an element in the document or 
     * returns null if the element does not exist
     * 
     * @param string $pointer
     * @return mixed|null
     */
    public function get($pointer)
    {
        $pointer = new Pointer($pointer);
        $data    = $pointer->evaluate($this->_properties);

        return $data;
    }

    /**
     * Checks whether this document is equal to the provided document
     * 
     * @param mixed $document
     * @return bool
     */
    public function equals($document)
    {
        return Comparator::compare($this, $document);
    }

    /**
     * Executes patch operations on this document
     * 
     * @param array $operations
     */
    public function patch(array $operations)
    {
        $patch = new Patch($operations);
        $data  = $patch->patch($this->_properties);

        $this->_properties = $data;
    }

    /**
     * Returns this document as json encoded string
     * 
     * @return string
     */
    public function toString()
    {
        return Parser::encode($this->_properties);
    }

    /**
     * @param string $file
     * @return \PSX\Json\Document
     */
    public static function fromFile($file)
    {
        return self::fromJson(file_get_contents($file));
    }

    /**
     * @param string $json
     * @return \PSX\Json\Document
     */
    public static function fromJson($json)
    {
        if (empty($json)) {
            throw new InvalidArgumentException('Provided JSON string must not be empty');
        }

        return self::fromStdClass(Parser::decode($json));
    }
}
