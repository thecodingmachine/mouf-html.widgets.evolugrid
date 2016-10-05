<?php


namespace Mouf\Html\Widgets\EvoluGrid\Utils;

/**
 * This class can transform an object into an array.
 * The array is built using public properties and getters.
 * The serializer is not called recursively on object.
 *
 * When retrieving a value as an array, the object will be queried on its public properties but also issers and getters.
 *
 *  $arr = (new ObjectToArrayCaster(get_class($obj)))->cast($obj);
 *
 *  $arr['foo'] // will return $obj->foo, or $obj->getFoo() or $obj->isFoo()
 *
 */
class ObjectToArrayCaster
{
    private $className;

    /**
     * @var \ReflectionClass
     */
    private $refClass;

    private $getters;

    /**
     * @param mixed $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
        $this->refClass = new \ReflectionClass($this->className);
        $this->getters = $this->getGetters();
    }

    /**
     * @return \ReflectionMethod[]
     */
    private function getGetters()
    {
        $methods = $this->refClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $selectedMethods = [];

        foreach ($methods as $method) {
            if ($method->getNumberOfParameters() === 0) {
                if (strpos($method->getName(), 'get') === 0) {
                    $selectedMethods[lcfirst(substr($method->getName(), 3))] = $method;
                } elseif (strpos($method->getName(), 'is') === 0) {
                    $selectedMethods[lcfirst(substr($method->getName(), 2))] = $method;
                }
            }
        }

        return $selectedMethods;
    }

    public function cast($object) : array
    {
        if (is_array($object)) {
            return $object;
        } elseif (!is_object($object)) {
            throw new \InvalidArgumentException('Argument passed to cast must be an array or an object.');
        }

        $arr = [];

        foreach ($this->refClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $arr[$property->getName()] = $property->getValue($object);
        }

        foreach ($this->getters as $propertyName => $method) {
            $arr[$propertyName] = $method->invoke($object);
        }

        return $arr;
    }
}
