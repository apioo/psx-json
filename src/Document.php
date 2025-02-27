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

use InvalidArgumentException;
use PSX\Record\Record;

/**
 * Document
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 * @template T
 * @extends \PSX\Record\Record<T>
 * @psalm-consistent-constructor
 * @psalm-consistent-templates
 */
class Document extends Record
{
    /**
     * Resolves the provided json pointer to an element in the document or returns null if the element does not exist
     *
     * @throws Exception\PointerException
     */
    public function pointer(string $pointer): mixed
    {
        return (new Pointer($pointer))->evaluate($this->properties);
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
     *
     * @throws Exception\PatchException
     */
    public function patch(array $operations)
    {
        $this->properties = (new Patch($operations))->patch($this->properties);
    }

    /**
     * Returns this document as json encoded string
     */
    public function toString(): string
    {
        return Parser::encode($this->properties);
    }
}
