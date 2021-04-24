<?php

namespace Arrilot\BitrixMigrationsFork\Utils;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
/**
 * Class Helper
 * @package Arrilot\BitrixMigrationsFork\Utils
 */
class Helper
{
    /**
     * Получает ID highload-блока по имени его таблицы.
     *
     * @param string $table Имя таблицы.
     * @return integer        ID.
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public static function getHighloadIdByTable($table)
    {
        $highloads = HighloadBlockTable::getTableName();
        $db = Application::getConnection();

        $result = $db->query("SELECT * FROM $highloads WHERE TABLE_NAME = '$table';");
        $result = $result->fetch();

        return $result['ID'];
    }
}