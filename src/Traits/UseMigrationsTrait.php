<?php

namespace Prokl\BitrixTestingTools\Traits;

/**
 * Trait UseMigrationsTrait
 * Использовать миграции.
 * @package Prokl\BitrixTestingTools\Traits
 *
 * @since 24.04.2021
 */
trait UseMigrationsTrait
{
    /**
     * Путь к директории с миграциями.
     *
     * @return string
     */
    protected function getMigrationsDir() : string
    {
        return '';
    }
}