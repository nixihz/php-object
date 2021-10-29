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

use ReflectionException;
use function explode;
use function str_replace;
use function json_decode;

class PhpObject
{

    /**
     * To json string
     *
     * @param int $flags
     * @param int $depth
     * @return string
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this, $flags, $depth);
    }

    /**
     * Parse json and bind parameters to the class；
     *
     * @param $jsonString
     * @return $this
     * @throws ReflectionException
     */
    public function fromJson($jsonString): PhpObject
    {
        $this->innerBind($this, json_decode($jsonString, true));
        return $this;
    }

    /**
     * User reflection to inner bind；
     *
     * @param $class
     * @param $params
     * @throws ReflectionException
     */
    private function innerBind(&$class, $params)
    {
        $reflect = new \ReflectionClass($class);
        $namespaceName = $reflect->getNamespaceName();
        $namespaceNameNodes = explode("\\", $namespaceName);

        $props = $reflect->getProperties();
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $propDoc = $prop->getDocComment();
            $matches = [];

            // todo pick up the Type graceful
            preg_match('/\/\*\* @var (.*) \*\//', $propDoc, $matches);
            $propType = $matches[1];

            if (!isset($params[$propName])) {

                // default value;
                if (strtoupper($propType) == 'STRING') {
                    $class->$propName = "";
                } elseif (strtoupper($propType) == 'INTEGER') {
                    $class->$propName = 0;
                }
            } else {
                if (!is_array($params[$propName])) {
                    $class->$propName = $params[$propName];
                } else {
                    $nnn = $namespaceNameNodes;

                    // get real prop type from Array
                    $typeName = str_replace([']', '['], '', $propType);

                    // array type has '[]' eg: Guest[]
                    $isArray = $typeName != $propType;
                    array_push($nnn, $typeName);
                    $className = implode("\\", $nnn);

                    if (class_exists($className, true)) {
                        if ($isArray) {

                            // handle array type
                            $nclasses = [];
                            if (isset($params[$propName][$typeName][0])) {
                                foreach ($params[$propName][$typeName] as $nParam) {
                                    $nclass = new $className();
                                    $this->innerBind($nclass, $nParam);
                                    $nclasses[] = $nclass;
                                }
                                $class->$propName = $nclasses;
                            } else {
                                if (isset($params[$propName][0]) || is_array($params[$propName])) {
                                    foreach ($params[$propName] as $nParam) {
                                        $nclass = new $className();
                                        $this->innerBind($nclass, $nParam);
                                        $nclasses[] = $nclass;
                                    }
                                    $class->$propName = $nclasses;
                                } else {
                                    $nclass = new $className();
                                    $this->innerBind($nclass, $params[$propName][$typeName]);
                                    $nclasses[] = $nclass;

                                    $class->$propName = $nclasses;
                                }
                            }
                        } else {
                            $nclass = new $className();
                            $this->innerBind($nclass, $params[$propName]);
                            $class->$propName = $nclass;
                        }
                    } else {
                        // The type declared in the comment is undefined or does not exist
                        throw new ReflectionException("The type declared in the comment is undefined or does not exist:" . $className);
                    }
                }
            }
        }
    }


    /**
     * To array
     *
     * @param bool $viaJsonDecode toArray via json_decode function
     * @return array|mixed
     */
    public function toArray($viaJsonDecode = true)
    {
        if ($viaJsonDecode) {
            return json_decode($this->toJson(), true);
        } else {
            return $this->innerToArray($this);
        }
    }

    /**
     * Inner to array
     *
     * @param $class
     * @return array|mixed
     * @throws ReflectionException
     */
    private function innerToArray(&$class)
    {
        if (empty($class)) {
            return $class;
        }
        $arr = [];
        $reflect = new \ReflectionClass($class);
        $nn = $reflect->getNamespaceName();
        $nnn = explode("\\", $nn);

        $props = $reflect->getProperties();
        foreach ($props as $prop) {
            $pname = $prop->getName();
            $pdoc = $prop->getDocComment();
            $matches = [];
            preg_match('/\/\*\* @var (.*) \*\//', $pdoc, $matches);
            $type = trim($matches[1]);

            $nnn3 = $nnn;
            $typeName = str_replace([']', '['], '', $type);
            $isArray = $typeName != $type;
            array_push($nnn3, $typeName);
            $className = implode("\\", $nnn3);
            if ($type == 'string' || $type == 'mixed' || $type == 'integer' || $type == 'boolean' || $type == 'float') {
                $arr[$pname] = $class->$pname;
            } else {
                if (class_exists($className)) {
                    if ($isArray) {
                        $tmpArr = [];
                        if (isset($class->$pname[0])) {
                            foreach ($class->$pname as $p) {
                                $tmpArr[] = $this->innerToArray($p);
                            }
                            $arr[$pname] = $tmpArr;
                        } else {
                            foreach ($class->$pname->$typeName as $p) {
                                $tmpArr[] = $this->innerToArray($p);
                            }
                            $arr[$pname] = [$typeName => $tmpArr];
                        }
                    } else {
                        $arr[$pname] = $this->innerToArray($class->$pname);
                    }

                }
            }
        }

        return $arr;
    }

}
