<?php

namespace Prokl\BitrixTestingTools\Base;

use Bitrix\Main\Application;
use Exception;
use Prokl\BitrixTestingTools\Migrations\ArrilotMigratorProcessor;
use Prokl\BitrixTestingTools\Migrator;
use Prokl\BitrixTestingTools\Helpers\ClassUtils;
use Prokl\BitrixTestingTools\Helpers\Database;
use Prokl\BitrixTestingTools\Traits\CustomDumpTrait;
use Prokl\BitrixTestingTools\Traits\ResetDatabaseTrait;
use Prokl\BitrixTestingTools\Traits\SprintMigrationsTrait;
use Prokl\BitrixTestingTools\Traits\UseMigrationsTrait;
use Prokl\TestingTools\Base\BaseTestCase;
use ReflectionClass;
use ReflectionException;
use Sheerockoff\BitrixCi\Bootstrap;

/**
 * Class BitrixableTestCase
 * @package Prokl\BitrixTestingTools\Base
 */
class BitrixableTestCase extends BaseTestCase
{
    protected $backupGlobalsBlacklist = ['DB'];

    /**
     * @var boolean $dropBase Сбрасывать ли базу после каждого теста.
     */
    private $dropBase = false;

    /**
     * @inheritDoc
     * @throws Exception
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

        // Миграции
        if ($this->useMigrations()) {
            $migrator = new ArrilotMigratorProcessor();
            /** @noinspection PhpUndefinedMethodInspection */
            $migrator->setMigrationsDir($this->getMigrationsDir())
                      ->init();

            $migrator->createMigrationsTable();
            $migrator->migrate();
        }

        // Миграции модуля sprint.option.
        if ($this->useSprintMigrations()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->sprintMigration();
        }
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
     * Эмуляция нахождения на какой-либо странице. Выставляется все, что связано с URL - как в D7,
     * так и в старом ядре.
     *
     * @param string $url URL.
     *
     * @return void
     *
     * @throws ReflectionException
     *
     * @since 15.06.2021
     */
    protected function goTo(string $url) : void
    {
        $_SERVER['REQUEST_URI'] = $url;
        $GLOBALS['APPLICATION']->sDirPath = GetPagePath(false, true);

        $application = Application::getInstance();

        $reflection = new ReflectionClass($application);
        $reflection_property = $reflection->getProperty('isExtendedKernelInitialized');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($application, false);

        $application->initializeExtendedKernel(
            [
                'server' => $_SERVER,
                'get' => $_GET,
                'post' => $_POST,
                'files' => $_FILES,
                'cookie' => $_COOKIE,
            ]
        );
    }

    /**
     * Битриксовые штучки-дрючки с буфером.
     *
     * @return void
     */
    private function clearBitrixBuffer() : void
    {
        $output = $GLOBALS['APPLICATION']->EndBufferContentMan();
        echo $output;
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
        return $this->hasTrait(ResetDatabaseTrait::class);
    }

    /**
     * Использовать ли кастомный дамп. Признак - трэйт CustomDumpTrait.
     *
     * @return boolean
     */
    private function useCustomDump() : bool
    {
        return $this->hasTrait(CustomDumpTrait::class);
    }

    /**
     * Использовать ли миграции. Признак - трэйт UseMigrationsTrait.
     *
     * @return boolean
     */
    private function useMigrations() : bool
    {
        return $this->hasTrait(UseMigrationsTrait::class);
    }

    /**
     * Использовать ли миграции модуля sprint.option. Признак - трэйт SprintMigrationsTrait.
     *
     * @return boolean
     */
    private function useSprintMigrations() : bool
    {
        return $this->hasTrait(SprintMigrationsTrait::class);
    }

    /**
     * Имеет ли экземпляр класса тот или иной трэйт.
     *
     * @param string $trait
     *
     * @return boolean
     */
    private function hasTrait(string $trait) : bool
    {
        $traits = ClassUtils::class_uses_recursive($this);

        return in_array($trait, $traits, true);
    }
}
