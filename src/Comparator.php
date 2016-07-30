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

use PSX\Record\RecordInterface;

/**
 * Comparator
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Comparator
{
    /**
     * Compares whether two values are equals. Uses the comparsion rules
     * described in the JSON patch RFC. Basically that means that the order of
     * elements in objects does not matter
     *
     * @see https://tools.ietf.org/html/rfc6902#section-4.6
     * @param mixed $left
     * @param mixed $right
     * @return boolean
     */
    public static function compare($left, $right)
    {
        if (self::isContainer($left) && self::isContainer($right)) {
            $leftFields  = self::normalize($left);
            $rightFields = self::normalize($right);

            if (count($leftFields) !== count($rightFields)) {
                return false;
            }

            foreach ($leftFields as $key => $value) {
                if (isset($rightFields[$key])) {
                    if (!self::compare($value, $rightFields[$key])) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            return true;
        } else {
            return $left === $right;
        }
    }

    protected static function isContainer($data)
    {
        return is_array($data) || $data instanceof \stdClass || $data instanceof RecordInterface;
    }

    protected static function normalize($data)
    {
        if (is_array($data)) {
            return $data;
        } elseif ($data instanceof \stdClass) {
            return (array) $data;
        } elseif ($data instanceof RecordInterface) {
            return $data->getProperties();
        } else {
            return null;
        }
    }
}
