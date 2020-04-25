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

namespace PSX\Json\Tests\Rpc;

use PHPUnit\Framework\TestCase;
use PSX\Json\Rpc\Builder;
use PSX\Json\Rpc\Exception\MethodNotFoundException;

/**
 * BuilderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class BuilderTest extends TestCase
{
    /**
     * @dataProvider callProvider
     */
    public function testCreateCall($method, $arguments, $id, $expect)
    {
        $return = (new Builder())->createCall($method, $arguments, $id);

        $this->assertJsonStringEqualsJsonString($expect, \json_encode($return));
    }

    public function callProvider()
    {
        return [
            ['foo', [1, 2], null, '{"jsonrpc":"2.0","method":"foo","params":[1,2],"id":null}'],
            ['foo', [1, 2], 1, '{"jsonrpc":"2.0","method":"foo","params":[1,2],"id":1}'],
        ];
    }

    /**
     * @dataProvider responseProvider
     */
    public function testCreateResponse($result, $id, $expect)
    {
        $return = (new Builder())->createResponse($result, $id);

        $this->assertJsonStringEqualsJsonString($expect, \json_encode($return));
    }

    public function responseProvider()
    {
        return [
            ['foo', null, '{"jsonrpc":"2.0","result":"foo","id":null}'],
            ['foo', 1, '{"jsonrpc":"2.0","result":"foo","id":1}'],
        ];
    }

    /**
     * @dataProvider errorProvider
     */
    public function testCreateError(\Throwable $exception, $id, $expect)
    {
        $return = (new Builder())->createError($exception, $id);

        $this->assertJsonStringEqualsJsonString($expect, \json_encode($return));
    }

    public function errorProvider()
    {
        return [
            [new MethodNotFoundException('Method not found'), null, '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":null}'],
            [new MethodNotFoundException('Method not found'), 1, '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":1}'],
        ];
    }
}
