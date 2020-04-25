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

namespace PSX\Json\Tests;

use PHPUnit\Framework\TestCase;
use PSX\Json\Document;

/**
 * DocumentTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DocumentTest extends TestCase
{
    public function testGet()
    {
        $document = Document::fromFile(__DIR__ . '/test_a.json');

        $this->assertEquals('bar', $document->get('/string'));
        $this->assertEquals(12, $document->get('/number'));
        $this->assertEquals(false, $document->get('/boolean'));
        $this->assertEquals(null, $document->get('/null'));
        $this->assertEquals(['foo'], $document->get('/array'));
        $this->assertEquals('foo', $document->get('/array/0'));
        $this->assertEquals([(object) ['foo' => 'bar']], $document->get('/arrayObject'));
        $this->assertEquals('bar', $document->get('/arrayObject/0/foo'));
        $this->assertEquals('bar', $document->get('/object/foo'));
    }

    public function testGetNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $document = Document::fromFile(__DIR__ . '/test_a.json');

        $this->assertEquals(null, $document->get('/array/1'));
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
