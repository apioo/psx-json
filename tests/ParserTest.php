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

namespace PSX\Json\Tests;

use PHPUnit\Framework\TestCase;
use PSX\Json\Parser;

/**
 * ParserTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class ParserTest extends TestCase
{
    public function testJsonEncode()
    {
        $actual = Parser::encode(['foo' => 'bar', 'test' => self::class, 'path' => '/population']);
        $expect = '{"foo": "bar", "test": "PSX\\\\Json\\\\Tests\\\\ParserTest", "path": "/population"}';

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testJsonDecode()
    {
        $val = '{"foo":"bar"}';

        $this->assertEquals(['foo' => 'bar'], Parser::decode($val, true));
    }

    public function testJsonDecodeAsArray()
    {
        $val = '{"foo":"bar"}';

        $this->assertEquals(['foo' => 'bar'], Parser::decodeAsArray($val));
    }

    public function testJsonDecodeAsObject()
    {
        $val = '{"foo":"bar"}';

        $this->assertEquals((object) ['foo' => 'bar'], Parser::decodeAsObject($val));
    }

    public function testJsonDecodeMalformed()
    {
        $this->expectException(\JsonException::class);

        $val = '{"foo":"bar"';

        Parser::decode($val);
    }

    public function testJsonDecodeControlCharacter()
    {
        $this->expectException(\JsonException::class);

        $val = '{"foo' . "\x02" . '":"bar"}';

        Parser::decode($val);
    }
}
