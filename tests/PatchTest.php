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
use PSX\Json\Exception\JsonException;
use PSX\Json\Patch;

/**
 * PatchTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class PatchTest extends TestCase
{
    /**
     * @dataProvider patchProvider
     */
    public function testPatch($doc, $patch, $expected, $error, $comment)
    {
        $data = (new Patch($patch))->patch($doc);

        if ($expected !== null) {
            $this->assertEquals($expected, $data, $comment);
        } else {
            $this->assertInstanceOf(\stdClass::class, $data, $comment);
        }
    }

    public function patchProvider()
    {
        return $this->getTestCases(false);
    }
    
    /**
     * @dataProvider patchErrorProvider
     */
    public function testPatchError($doc, $patch, $expected, $error, $comment)
    {
        $this->expectException(JsonException::class);

        (new Patch($patch))->patch($doc);
    }

    public function patchErrorProvider()
    {
        return $this->getTestCases(true);
    }

    private function getTestCases($includeError)
    {
        $tests  = json_decode(file_get_contents(__DIR__ . '/patch_tests.json'));
        $result = [];

        foreach ($tests as $testCase) {
            $error    = $testCase->error ?? null;
            $expected = $testCase->expected ?? null;
            $disabled = $testCase->disabled ?? null;

            if ($disabled) {
                continue;
            }

            if (($includeError && $error !== null) || (!$includeError && $expected !== null)) {
                $result[] = [
                    $testCase->doc,
                    $testCase->patch,
                    $expected,
                    $error,
                    $testCase->comment ?? '',
                ];
            }
        }

        return $result;
    }
}
