<?php

namespace Prokl\BitrixTestingTools;

use InvalidArgumentException;
use Sheerockoff\BitrixCi\SqlDump;

/**
 * Class Migrator
 * Декоратор к мигратору дампов (позволяет загружать свои).
 * @package Prokl\BitrixTestingTools
 *
 * @since 24.04.2021
 */
class Migrator
{
    /**
     * @param string $pathDump Путь к дампу базы.
     *
     * @return void
     */
    public static function migrate(string $pathDump) : void
    {
        $db = mysqli_connect(
            getenv('MYSQL_HOST', true) ?: getenv('MYSQL_HOST'),
            getenv('MYSQL_USER', true) ?: getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD', true) ?: getenv('MYSQL_PASSWORD'),
            getenv('MYSQL_DATABASE', true) ?: getenv('MYSQL_DATABASE')
        );

        if (!$db) {
            throw new InvalidArgumentException('Mysql connection error.');
        }

        $sqlDump = new SqlDump($pathDump);
        foreach ($sqlDump->parse() as $query) {
            mysqli_query($db, $query);
        }

        mysqli_close($db);
    }
}