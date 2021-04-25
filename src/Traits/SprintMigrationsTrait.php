<?php

namespace Prokl\BitrixTestingTools\Traits;

use Bitrix\Main\ModuleManager;
use CModule;
use Sprint\Migration\Exceptions\MigrationException;
use Sprint\Migration\Installer;

/**
 * Trait SprintMigrationsTrait
 * Трэйт запуска миграций модуля sprint.migration.
 * @package Prokl\BitrixTestingTools\Traits
 */
trait SprintMigrationsTrait
{
    /**
     * Запуск миграций модуля sprint.option (@see https://github.com/andreyryabin/sprint.migration).
     *
     * @return boolean
     * @throws MigrationException
     */
    protected function sprintMigration() : bool
    {
        if (!ModuleManager::isModuleInstalled('sprint.migration')) {
            RegisterModule('sprint.migration');
        }

        if (CModule::IncludeModule('sprint.migration')) {
            (new Installer(
                [
                    'migration_dir'          => $this->getPathSprintMigrations(),
                    'migration_dir_absolute' => true,
                ]
            ))->up();
            return true;
        }

        return false;
    }

    /**
     * Путь к миграциям.
     *
     * @return string
     */
    protected function getPathSprintMigrations() : string
    {
        return  __DIR__ . '../../../../../../Tests/sprint_migrations/';
    }

}
