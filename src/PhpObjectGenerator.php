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


use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Property;

class PhpObjectGenerator
{

    const KEEP_CASE = 0;
    const SNAKE_CASE = 1;
    const CAMEL_CASE = 2;

    const KEEP_MODEL = 0;
    const SINGLE_MODEL = 1;

    public $fieldCase = 2;
    // todo support plural model
    public $pluralModel = 0;
    public $path = ".";
    public $baseClassName = "BaseClassName";
    public $namespace = "common";

    /** @var PhpFile[] */
    public $files = [];

    public function __construct()
    {
    }

    public function parseJson($jsonString = "")
    {
        $json = json_decode($jsonString);
        if ($json == null) {
            echo "Not a valid json, please check it out!";
            exit;
        }
        $this->genObject($this->baseClassName, $json);
    }

    public function echoFiles()
    {
        foreach ($this->files as $index => $file) {
            echo $file . PHP_EOL . PHP_EOL;
        }
    }

    public function save()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        foreach ($this->files as $index => $file) {
            $namespaces = $file->getNamespaces();
            $namespace = array_pop($namespaces);
            $classes = $namespace->getClasses();
            $class = array_pop($classes);
            $filename = $this->path . DIRECTORY_SEPARATOR . $class->getName() . ".php";
            $res = file_put_contents($filename, $file);
            if ($res) {
                echo "Class " . $class->getName() . " saved at: " . $filename . PHP_EOL;
            }
        }
    }

    private function genObject($className, $fields)
    {
        $subFields = $fields;
        if (is_object($fields)) {

        } elseif (is_array($fields)) {
            $subFields = $fields[0];
        }
        $file = new PhpFile();
        $file->addComment('This file is auto-generated by php-object.');
        $namespace = $file->addNamespace($this->namespace);
        $class = $namespace->addClass($this->getClassName($className));
        $class->setExtends(PhpObject::class);
        foreach ($subFields as $field => $subFf) {
            $property = new Property($this->getFieldName($field));
            $simpleType = $this->getSimpleType($subFf);
            if ($simpleType) {
                // PHP@7.4 support
                // $property->setType($simpleType);
                $property->setComment('@var ' . $simpleType);
            }
            if (is_object($subFf)) {
                $property->setComment('@var ' . $this->getClassName($field));
                $this->genObject($field, $subFf);
            }
            if (is_array($subFf)) {
                if (count($subFf) <= 0) {
                    continue;
                }
                $simpleType = $this->getSimpleType($subFf[0]);
                if ($simpleType) {
                    $property->setComment('@var ' . $simpleType . "[]");
                } else {
                    $property->setComment('@var ' . $this->getClassName($field) . "[]");
                    $this->genObject($field, $subFf);
                }
            }

            $class->addMember($property);
        }

        $this->files[] = $file;
    }

    private function getSimpleType($value)
    {
        $type = "";
        if (is_string($value)) {
            $type = 'string';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (is_float($value)) {
            $type = 'float';
        } elseif (is_bool($value)) {
            $type = 'bool';
        }
        return $type;
    }

    private function getClassName($name)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $name));
        return str_replace(' ', '', $value);
    }

    private function getFieldName($name)
    {
        switch ($this->fieldCase) {
            case self::CAMEL_CASE:
                $value = ucwords(str_replace(['-', '_'], ' ', $name));
                return lcfirst(str_replace(' ', '', $value));
            case self::SNAKE_CASE:
                // todo support snake case
                return $name;
            case self::KEEP_CASE:
            default:
                return $name;
        }
    }

}
