<?php

namespace Prokl\BitrixTestingTools\Base;

use Prokl\TestingTools\Base\BaseTestCase;

/**
 * Class BitrixableTestCase
 * @package Prokl\BitrixTestingTools\Base
 */
class BitrixableTestCase extends BaseTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        putenv('MYSQL_HOST=localhost');
        putenv('MYSQL_DATABASE=bitrix_ci');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASSWORD=');

        if (!$this->checkExistBaseContent()) {
            \Sheerockoff\BitrixCi\Bootstrap::migrate();
        }

        \Sheerockoff\BitrixCi\Bootstrap::bootstrap();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Битриксовые штучки-дрючки с буфером.
        while (ob_get_level()) {
            ob_end_clean();
        }

        if ($GLOBALS['APPLICATION']) {
            $GLOBALS['APPLICATION']->RestartBuffer();
        }
    }

    /**
     * Проверка - база не пустая ли.
     *
     * @return boolean
     */
    protected function checkExistBaseContent() : bool
    {
        $db = mysqli_connect(
            getenv('MYSQL_HOST', true) ?: getenv('MYSQL_HOST'),
            getenv('MYSQL_USER', true) ?: getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD', true) ?: getenv('MYSQL_PASSWORD'),
            getenv('MYSQL_DATABASE', true) ?: getenv('MYSQL_DATABASE')
        );

        if (!$db) {
            throw new \InvalidArgumentException('Mysql connection error.');
        }

        $result = false;
        if (mysqli_query($db,"DESCRIBE b_users ")){
            $result = true;
        }

        mysqli_close($db);
        return $result;
    }
}