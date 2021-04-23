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

        // \Sheerockoff\BitrixCi\Bootstrap::migrate();
        \Sheerockoff\BitrixCi\Bootstrap::bootstrap();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        ob_get_clean(); // Битриксовые штучки-дрючки.
    }
}