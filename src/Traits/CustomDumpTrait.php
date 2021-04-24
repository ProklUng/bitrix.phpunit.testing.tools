<?php

namespace Prokl\BitrixTestingTools\Traits;

/**
 * Trait CustomDumpTrait
 * Использовать свой дамп базы для тестов. Применяется в сочетании с трэйтом ResetDatabaseTrait.
 * @package Prokl\BitrixTestingTools\Traits
 *
 * @since 24.04.2021
 */
trait CustomDumpTrait
{
    /**
     * Путь к кастомному дампу БД.
     *
     * @return string
     */
    protected function getDumpPath() : string
    {
        return '';
    }
}