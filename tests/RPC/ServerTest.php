<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Json\Tests\RPC;

use PHPUnit\Framework\TestCase;
use PSX\Json\RPC\Exception\InvalidRequestException;
use PSX\Json\RPC\Exception\MethodNotFoundException;
use PSX\Json\RPC\Exception\ParseErrorException;
use PSX\Json\RPC\Server;

/**
 * ServerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ServerTest extends TestCase
{
    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke($request, $expect, $callable)
    {
        $invoker = new Server($callable);
        $actual  = $invoker->invoke(\json_decode($request));

        $this->assertJsonStringEqualsJsonString($expect, \json_encode($actual));
    }
    
    public function invokeProvider()
    {
        return [
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}',
                '{"jsonrpc": "2.0", "result": 19, "id": 1}',
                function($method, $arguments){
                    return $arguments[0] - $arguments[1];
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}',
                '{"jsonrpc": "2.0", "result": -19, "id": 2}',
                function($method, $arguments){
                    return $arguments[0] - $arguments[1];
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}',
                '{"jsonrpc": "2.0", "result": 19, "id": 3}',
                function($method, $arguments){
                    return $arguments->minuend - $arguments->subtrahend;
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}',
                '{"jsonrpc": "2.0", "result": 19, "id": 4}',
                function($method, $arguments){
                    return $arguments->minuend - $arguments->subtrahend;
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
                '{"jsonrpc":"2.0","result":null,"id":null}',
                function($method, $arguments){
                    return null;
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar"}',
                '{"jsonrpc":"2.0","result":null,"id":null}',
                function($method, $arguments){
                    return null;
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
                '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}',
                function($method, $arguments){
                    throw new MethodNotFoundException('Method not found');
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
                '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}',
                function($method, $arguments){
                    throw new ParseErrorException('Parse error');
                }
            ],
            [
                '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
                '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}',
                function($method, $arguments){
                    throw new InvalidRequestException('Invalid Request');
                }
            ],
            [
                '[
  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
  {"jsonrpc": "2.0", "method"
]',
                '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}',
                function($method, $arguments){
                    throw new ParseErrorException('Parse error');
                }
            ],
            [
                '[]',
                '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}',
                function($method, $arguments){
                    throw new InvalidRequestException('Invalid Request');
                }
            ],
            [
                '[1]',
                '[
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
]',
                function($method, $arguments){
                    throw new InvalidRequestException('Invalid Request');
                }
            ],
            [
                '[1,2,3]',
                '[
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
]',
                function($method, $arguments){
                    throw new InvalidRequestException('Invalid Request');
                }
            ],
        ];
    }
}
