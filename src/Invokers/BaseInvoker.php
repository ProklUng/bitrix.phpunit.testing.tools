<?php

namespace Prokl\BitrixTestingTools\Invokers;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class BaseInvoker
 * @package Prokl\BitrixTestingTools\Invokers
 */
abstract class BaseInvoker
{
    /**
     * Исполнить.
     *
     * @return mixed
     */
    abstract public function execute();

    /**
     * Выполнить метод.
     *
     * @param object     $object Объект.
     * @param string     $method Метод.
     * @param array|null $params Параметры.
     *
     * @return mixed
     * @throws ReflectionException Ошибки рефлексии.
     */
    protected function invokeMethod($object, string $method, array $params = null)
    {
        $method = new ReflectionMethod($object, $method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params ? $params : []);
    }

    /**
     * Установить свойство объекта.
     *
     * @param object     $object   Объект.
     * @param string     $property Свойство.
     * @param mixed|null $value    Значение.
     *
     * @throws ReflectionException Ошибки рефлексии.
     */
    protected static function setObjectPropertyValue($object, string $property, $value = null)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Получить свойство объекта.
     *
     * @param object $object   Объект.
     * @param string $property Свойство.
     *
     * @return mixed
     * @throws ReflectionException Ошибки рефлексии.
     */
    protected function getObjectPropertyValue($object, string $property)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
