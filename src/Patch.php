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
use PSX\Record\RecordInterface;

/**
 * Class to apply patch operations on a json object. Based on the json-patch-php
 * library but works with stdClass instead of associative arrays
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 * @see     https://github.com/mikemccabe/json-patch-php
 * @see     https://tools.ietf.org/html/rfc6902
 */
class Patch
{
    private array $operations;

    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    public function patch($data)
    {
        foreach ($this->operations as $operation) {
            $op    = $operation->op ?? null;
            $path  = $operation->path ?? null;
            $value = $operation->value ?? null;
            $from  = $operation->from ?? null;

            switch ($op) {
                case 'add':
                case 'append':
                case 'replace':
                    if (!property_exists($operation, 'value')) {
                        throw new InvalidArgumentException('Value not available');
                    }

                    $pointer = new Pointer($path);
                    $data    = $this->doOperation($data, $pointer->getParts(), $op, $path, $value);
                    break;

                case 'remove':
                    $pointer = new Pointer($path);
                    $data    = $this->doOperation($data, $pointer->getParts(), $op, $path, null);
                    break;

                case 'test':
                    if (!property_exists($operation, 'value')) {
                        throw new InvalidArgumentException('Value not available');
                    }

                    $pointer = new Pointer($path);
                    $actual  = $pointer->evaluate($data);

                    if (!Comparator::compare($value, $actual)) {
                        throw new InvalidArgumentException('Test value is different');
                    }
                    break;

                case 'copy':
                    if (!property_exists($operation, 'from')) {
                        throw new InvalidArgumentException('From not available');
                    }

                    $pointer = new Pointer($from);
                    $value   = $pointer->evaluate($data);

                    $pointer = new Pointer($path);
                    $data    = $this->doOperation($data, $pointer->getParts(), 'add', $path, $value);
                    break;

                case 'move':
                    if (!property_exists($operation, 'from')) {
                        throw new InvalidArgumentException('From not available');
                    }

                    $pointer = new Pointer($from);
                    $value   = $pointer->evaluate($data);
                    $data    = $this->doOperation($data, $pointer->getParts(), 'remove', $path, null);
                    
                    $pointer = new Pointer($path);
                    $data    = $this->doOperation($data, $pointer->getParts(), 'add', $path, $value);
                    break;

                default:
                    throw new InvalidArgumentException('Invalid operator');
                    break;
            }
        }

        return $data;
    }

    protected function doOperation($data, array $parts, $op, $path, $value)
    {
        if (count($parts) == 0) {
            if ($op == 'add' || $op == 'replace') {
                return $value;
            }
        }

        $part = array_shift($parts);

        if (count($parts) > 0) {
            if (is_array($data)) {
                if (array_key_exists($part, $data)) {
                    $data[$part] = $this->doOperation($data[$part], $parts, $op, $path, $value);
                } else {
                    throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                }
            } elseif ($data instanceof \stdClass) {
                if (property_exists($data, $part)) {
                    $data->$part = $this->doOperation($data->$part, $parts, $op, $path, $value);
                } else {
                    throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                }
            } elseif ($data instanceof RecordInterface) {
                if ($data->hasProperty($part)) {
                    $data->setProperty($part, $this->doOperation($data->getProperty($part), $parts, $op, $path, $value));
                } else {
                    throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                }
            } else {
                throw new InvalidArgumentException('Invalid path ' . $path);
            }

            return $data;
        }

        if (is_array($data)) {
            if ($part == '-' || preg_match('/^(0|[1-9][0-9]*)$/', $part)) {
                if ($op == 'add' || $op == 'append') {
                    if ($part == '-') {
                        $data[] = $value;
                    } else {
                        $index = intval($part);
                        if ($index >= 0 && $index <= count($data)) {
                            array_splice($data, $index, 0, [$value]);
                        } else {
                            throw new InvalidArgumentException('Key ' . $index . ' does not exist at /' . implode('/', $parts));
                        }
                    }
                } elseif ($op == 'replace') {
                    if (array_key_exists($part, $data)) {
                        $data[$part] = $value;
                    }
                } elseif ($op == 'remove') {
                    if (array_key_exists($part, $data)) {
                        unset($data[$part]);
                        $data = array_values($data);
                    } else {
                        throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                    }
                }
            } else {
                throw new InvalidArgumentException('Invalid key at /' . implode('/', $parts));
            }
        } elseif ($data instanceof \stdClass) {
            if ($part !== '') {
                if ($op == 'add' || $op == 'append') {
                    $data->$part = $value;
                } elseif ($op == 'replace') {
                    if (property_exists($data, $part)) {
                        $data->$part = $value;
                    }
                } elseif ($op == 'remove') {
                    if (property_exists($data, $part)) {
                        unset($data->$part);
                    } else {
                        throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                    }
                }
            }
        } elseif ($data instanceof RecordInterface) {
            if ($part !== '') {
                if ($op == 'add' || $op == 'append') {
                    $data->setProperty($part, $value);
                } elseif ($op == 'replace') {
                    if ($data->hasProperty($part)) {
                        $data->setProperty($part, $value);
                    }
                } elseif ($op == 'remove') {
                    if ($data->hasProperty($part)) {
                        $data->removeProperty($part);
                    } else {
                        throw new InvalidArgumentException('Property ' . $part . ' does not exist at /' . implode('/', $parts));
                    }
                }
            }
        }

        return $data;
    }
}
