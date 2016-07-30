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

namespace PSX\Json\Tests;

use PSX\Record\Record;
use PSX\Json\Comparator;
use PSX\Json\Document;

/**
 * DocumentTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $docA = Document::fromFile(__DIR__ . '/test_a.json');

        $this->assertEquals('bar', $docA->get('/string'));
        $this->assertEquals(12, $docA->get('/number'));
        $this->assertEquals(false, $docA->get('/boolean'));
        $this->assertEquals(null, $docA->get('/null'));
        $this->assertEquals(['foo'], $docA->get('/array'));
        $this->assertEquals('foo', $docA->get('/array/0'));
        $this->assertEquals(null, $docA->get('/array/1'));
        $this->assertEquals([(object) ['foo' => 'bar']], $docA->get('/arrayObject'));
        $this->assertEquals('bar', $docA->get('/arrayObject/0/foo'));
        $this->assertEquals(null, $docA->get('/arrayObject/0/bar'));
        $this->assertEquals('bar', $docA->get('/object/foo'));
        $this->assertEquals(null, $docA->get('/object/bar'));
    }
    
    public function testEquals()
    {
        $docA = Document::fromFile(__DIR__ . '/test_a.json');
        $docB = Document::fromFile(__DIR__ . '/test_b.json');

        $this->assertFalse($docA->equals($docB));
        $this->assertTrue($docA->equals($docA));
    }

    public function testPatch()
    {
        $docA = Document::fromFile(__DIR__ . '/test_a.json');
        $docA->patch([
            (object) ['op' => 'add', 'path' => '/array/-', 'value' => 'bar'],
            (object) ['op' => 'add', 'path' => '/object/bar', 'value' => 'bar'],
        ]);

        $this->assertEquals(['foo', 'bar'], $docA->get('/array'));
        $this->assertEquals((object) ['foo' => 'bar', 'bar' => 'bar'], $docA->get('/object'));
    }

    public function testToString()
    {
        $docA   = Document::fromFile(__DIR__ . '/test_a.json');
        $expect = file_get_contents(__DIR__ . '/test_a.json');

        $this->assertJsonStringEqualsJsonString($expect, $docA->toString());
    }
}
