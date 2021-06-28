<?php

namespace Arrilot\BitrixMigrationsFork;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

/**
 * Class Helpers
 * @package Arrilot\BitrixMigrationsFork
 */
class Helpers
{
    /**
     * @var array $hls
     */
    protected static $hls = [];

    /**
     * @var array $ufs
     */
    protected static $ufs = [];

    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     *
     * @return string
     */
    public static function studly(string $value) : string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Рекурсивный поиск миграций с поддирректориях.
     *
     * @param string  $pattern
     * @param integer $flags Does not support flag GLOB_BRACE.
     *
     * @return array
     */
    public static function rGlob(string $pattern, int $flags = 0) : array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::rGlob($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Получить ID HL по названию таблицы.
     *
     * @param string $table_name
     *
     * @return mixed
     * @throws SqlQueryException
     */
    public static function getHlId(string $table_name)
    {
        if (!isset(static::$hls[$table_name])) {
            $dbRes = Application::getConnection()->query('SELECT `ID`, `NAME`, `TABLE_NAME` FROM b_hlblock_entity');
            while ($block = $dbRes->fetch()) {
                static::$hls[$block['TABLE_NAME']] = $block;
            }
        }

        return static::$hls[$table_name]['ID'];
    }

    /**
     * Получить ID UF.
     *
     * @param $obj
     * @param string $field_name
     *
     * @return mixed
     * @throws SqlQueryException
     */
    public static function getFieldId($obj, string $field_name)
    {
        if (!isset(static::$ufs[$obj][$field_name])) {
            $dbRes = Application::getConnection()->query('SELECT * FROM b_user_field');
            while ($uf = $dbRes->fetch()) {
                static::$ufs[$uf['ENTITY_ID']][$uf['FIELD_NAME']] = $uf;
            }
        }

        return static::$ufs[$obj][$field_name]['ID'];
    }
}
