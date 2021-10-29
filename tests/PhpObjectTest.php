<?php
/*
 * This file is part of nixihz/php-object
 *
 * (c) Nixihz <yagoodidea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nixihz\PhpObject;

use PHPUnit\Framework\TestCase;
use Nixihz\PhpObject\TestFixture\DemoClass;

/**
 * @covers \Nixihz\PhpObject\PhpObject
 */
final class PhpObjectTest extends TestCase
{

    public function testFromJsonAndBuild()
    {
        $demo = new DemoClass();
        $foobarString = <<<JSON
{
    "foobar": {
        "foo": "hello world",
        "bar": 6100
    },
    "foobars": [
        {
            "foo": "hello",
            "bar": 61
        },
        {
            "foo": "world",
            "bar": 6616111
        }
    ]
}
JSON;
        $this->assertEquals([
            'foobar' => ['foo' => 'hello world', 'bar' => 6100,],
            'foobars' => [
                ['foo' => 'hello', 'bar' => 61,],
                ['foo' => 'world', 'bar' => 6616111,],
            ],
        ],
            $demo->fromJson($foobarString)->toArray(false)
        );
    }

    public function testGenerator()
    {
        $jsonString = <<<HH
{
    "foo_bar": {
        "foo": 1.3,
        "bar": [
            6100,
            6100
        ]
    },
    "f_oo_bars": [
        {
            "foo": "hello",
            "bar": 61
        },
        {
            "foo": "world",
            "bar": 6616111
        }
    ]
}
HH;
        $generator = new PhpObjectGenerator();
        $generator->parseJson($jsonString);

        $this->assertEquals(count($generator->files), 3);
    }

}