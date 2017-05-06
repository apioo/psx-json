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

namespace PSX\Json\Tests;

use PSX\Json\Patch;

/**
 * PatchTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class PatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider patchTestProvider
     */
    public function testPatch($doc, $patch, $expected, $error, $comment)
    {
        $patch = new Patch($patch);

        if ($expected !== null) {
            $data = $patch->patch($doc);

            $this->assertEquals($expected, $data, $comment);
        } elseif ($error !== null) {
            try {
                $data = $patch->patch($doc);

                $this->fail($error);
            } catch (\Exception $e) {
                // must throw an exception
            }
        }
    }

    public function patchTestProvider()
    {
        $tests  = json_decode(file_get_contents(__DIR__ . '/patch_tests.json'));
        $result = [];

        foreach ($tests as $testCase) {
            $result[] = [
                $testCase->doc,
                $testCase->patch,
                isset($testCase->expected) ? $testCase->expected : null,
                isset($testCase->error) ? $testCase->error : null,
                isset($testCase->comment) ? $testCase->comment : null,
            ];
        }

        return $result;
    }
}
