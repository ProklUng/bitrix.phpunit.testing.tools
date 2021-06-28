<?php

namespace Arrilot\BitrixMigrationsFork\Constructors;

/**
 * Trait FieldConstructor
 * @package Arrilot\BitrixMigrationsFork\Constructors
 */
trait FieldConstructor
{
    /**
     * @var array $fields
     */
    public $fields = [];

    /**
     * @var array $defaultFields
     */
    public static $defaultFields = [];

    /**
     * Получить итоговые настройки полей.
     */
    public function getFieldsWithDefault() : array
    {
        return array_merge((array)static::$defaultFields[get_called_class()], $this->fields);
    }
}