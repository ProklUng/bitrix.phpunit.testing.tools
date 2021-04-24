<?php

namespace Prokl\BitrixTestingTools\Base;

use Prokl\BitrixTestingTools\Migrator;
use Prokl\BitrixTestingTools\Helpers\ClassUtils;
use Prokl\BitrixTestingTools\Helpers\Database;
use Prokl\BitrixTestingTools\Traits\CustomDumpTrait;
use Prokl\BitrixTestingTools\Traits\ResetDatabaseTrait;
use Prokl\TestingTools\Base\BaseTestCase;
use Sheerockoff\BitrixCi\Bootstrap;

/**
 * Class BitrixableTestCase
 * @package Prokl\BitrixTestingTools\Base
 */
class BitrixableTestCase extends BaseTestCase
{
    /**
     * @var boolean $dropBase Сбрасывать ли базу после каждого теста.
     */
    private $dropBase = false;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupDatabaseData();
        $this->dropBase = $this->needDropBase();
        $dbManager = $this->getDbManager();

        if ($this->dropBase) {
            $dbManager->dropBase();
            $dbManager->createDatabaseIfNotExist();
            $this->migrateDatabase();
        } else {
            $dbManager->createDatabaseIfNotExist();

            if ($dbManager->hasEmptyBase()) {
                $this->migrateDatabase();
            }
        }

        Bootstrap::bootstrap();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearBitrixBuffer();

        if ($this->dropBase) {
            $dbManager = $this->getDbManager();
            $dbManager->dropBase();
        }
    }

    /**
     * Параметры подключения к тестовой базе.
     *
     * @return void
     */
    protected function setupDatabaseData() : void
    {
        putenv('MYSQL_HOST=localhost');
        putenv('MYSQL_DATABASE=bitrix_ci');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASSWORD=');
    }

    /**
     * Битриксовые штучки-дрючки с буфером.
     *
     * @return void
     */
    private function clearBitrixBuffer() : void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        if ($GLOBALS['APPLICATION']) {
            $GLOBALS['APPLICATION']->RestartBuffer();
        }
    }

    /**
     * Загрузить дамп - кастомный или нативный.
     *
     * @return void
     */
    private function migrateDatabase() : void
    {
        $this->useCustomDump() ? Migrator::migrate($this->getDumpPath()) : Bootstrap::migrate();
    }

    /**
     * Экземпляр менеджера БД.
     *
     * @return Database
     */
    private function getDbManager() : Database
    {
        return new Database(
            getenv('MYSQL_HOST', true) ?: getenv('MYSQL_HOST'),
            getenv('MYSQL_DATABASE', true) ?: getenv('MYSQL_DATABASE'),
            getenv('MYSQL_USER', true) ?: getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD', true) ?: getenv('MYSQL_PASSWORD')
        );
    }

    /**
     * Нужно ли сбрасывать базу. Признак - трэйт ResetDatabaseTrait.
     *
     * @return boolean
     */
    private function needDropBase() : bool
    {
        $traits = ClassUtils::class_uses_recursive($this);

        return in_array(ResetDatabaseTrait::class, $traits, true);
    }

    /**
     * Использовать ли кастомный дамп. Признак - трэйт CustomDumpTrait.
     *
     * @return boolean
     */
    private function useCustomDump() : bool
    {
        $traits = ClassUtils::class_uses_recursive($this);

        return in_array(CustomDumpTrait::class, $traits, true);
    }
}