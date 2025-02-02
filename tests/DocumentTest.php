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

namespace PSX\Json\Tests;

use PHPUnit\Framework\TestCase;
use PSX\Json\Document;
use PSX\Json\Exception\PointerException;

/**
 * DocumentTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class DocumentTest extends TestCase
{
    public function testPointer()
    {
        $document = $this->fromFile(__DIR__ . '/test_a.json');

        $this->assertEquals('bar', $document->pointer('/string'));
        $this->assertEquals(12, $document->pointer('/number'));
        $this->assertEquals(false, $document->pointer('/boolean'));
        $this->assertEquals(null, $document->pointer('/null'));
        $this->assertEquals(['foo'], $document->pointer('/array'));
        $this->assertEquals('foo', $document->pointer('/array/0'));
        $this->assertEquals([(object) ['foo' => 'bar']], $document->pointer('/arrayObject'));
        $this->assertEquals('bar', $document->pointer('/arrayObject/0/foo'));
        $this->assertEquals('bar', $document->pointer('/object/foo'));
    }

    public function testPointerNotExists()
    {
        $this->expectException(PointerException::class);

        $document = $this->fromFile(__DIR__ . '/test_a.json');

        $this->assertEquals(null, $document->pointer('/array/1'));
    }

    public function testEquals()
    {
        $docA = $this->fromFile(__DIR__ . '/test_a.json');
        $docB = $this->fromFile(__DIR__ . '/test_b.json');

        $this->assertFalse($docA->equals($docB));
        $this->assertTrue($docA->equals($docA));
    }

    public function testPatch()
    {
        $docA = $this->fromFile(__DIR__ . '/test_a.json');
        $docA->patch([
            (object) ['op' => 'add', 'path' => '/array/-', 'value' => 'bar'],
            (object) ['op' => 'add', 'path' => '/object/bar', 'value' => 'bar'],
        ]);

        $this->assertEquals(['foo', 'bar'], $docA->pointer('/array'));
        $this->assertEquals((object) ['foo' => 'bar', 'bar' => 'bar'], $docA->pointer('/object'));
    }

    public function testToString()
    {
        $docA   = $this->fromFile(__DIR__ . '/test_a.json');
        $expect = file_get_contents(__DIR__ . '/test_a.json');

        $this->assertJsonStringEqualsJsonString($expect, $docA->toString());
    }

    private function fromFile(string $file): Document
    {
        return Document::from(json_decode(file_get_contents($file)));
    }
}
