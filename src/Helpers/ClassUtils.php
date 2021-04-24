<?php

namespace Prokl\BitrixTestingTools\Helpers;

/**
 * Class ClassUtils
 * @package Prokl\BitrixTestingTools\Helpers
 * Форкнуто из Laravel Support.
 * @since 24.04.2021
 */
class ClassUtils
{
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param  object|string  $class
     * @return array
     */
    public static function class_uses_recursive($class) : array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += self::trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  string  $trait
     * @return array
     */
    public static function trait_uses_recursive($trait) : array
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += self::trait_uses_recursive($trait);
        }

        return $traits;
    }
}