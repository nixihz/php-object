<?php
/*
 * This file is part of nixihz/php-object
 *
 * (c) Nixihz <yagoodidea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nixihz\PhpObject\TestFixture;

use Nixihz\PhpObject\PhpObject;

class DemoClass extends PhpObject
{
    /** @var FooBarClass */
    public $foobar;

    /** @var FooBarClass[] */
    public $foobars;

}