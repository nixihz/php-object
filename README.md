# nixihz/php-object

Allows generate class files parse from json and map json to php object, including multi-level and complex objects;

## Installation

You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

```
composer require nixihz/php-object
```

If you only need this library during development, for instance to run your project's test suite, then you should add it
as a development-time dependency:

```
composer require --dev nixihz/php-object
```

## Feature

- [x] Map json to object
- [ ] Generate PHP Class files from json

## Map json to object 
`fromJson` map json to object

```php

class FooBarClass extends PhpObject
{
    /** @var string */
    public $foo;

    /** @var integer */
    public $bar;
}

class DemoClass extends PhpObject
{
    /** @var FooBarClass */
    public $foobar;

    /** @var FooBarClass[] */
    public $foobars;

}

$json = '{"foobar":{"foo":"hello world","bar":64100},"foobars":[{"foo":"hello","bar":641},{"foo":"world","bar":664411}]}';
$demo = (new DemoClass())->fromJson($json);

var_export($demo);

```

outputs:
```
DemoClass::__set_state(array(
   'foobar' => 
      FooBarClass::__set_state(array(
         'foo' => 'hello world',
         'bar' => 64100,
      )),
   'foobars' => 
      array (
        0 => 
        FooBarClass::__set_state(array(
           'foo' => 'hello',
           'bar' => 641,
        )),
        1 => 
        FooBarClass::__set_state(array(
           'foo' => 'world',
           'bar' => 664411,
        )),
      ),
))

```
