<?php


namespace Arrilot\BitrixMigrationsFork\Constructors;

use Arrilot\BitrixMigrationsFork\Helpers;
use Arrilot\BitrixMigrationsFork\Logger;
use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Exception;

/**
 * Class HighloadBlock
 * @package Arrilot\BitrixMigrationsFork\Constructors
 */
class HighloadBlock
{
    use FieldConstructor;

    public $lang;

    /**
     * Добавить HL.
     *
     * @throws Exception
     */
    public function add()
    {
        $result = HighloadBlockTable::add($this->getFieldsWithDefault());

        if (!$result->isSuccess()) {
            throw new Exception(join(', ', $result->getErrorMessages()));
        }

        foreach ($this->lang as $lid => $name) {
            HighloadBlockLangTable::add([
                'ID' => $result->getId(),
                'LID' => $lid,
                'NAME' => $name
            ]);
        }

        Logger::log("Добавлен HL {$this->fields['NAME']}", Logger::COLOR_GREEN);

        return $result->getId();
    }

    /**
     * Обновить HL.
     *
     * @param string $table_name
     *
     * @throws Exception
     */
    public function update(string $table_name) : void
    {
        $id = Helpers::getHlId($table_name);
        $result = HighloadBlockTable::update($id, $this->fields);

        if (!$result->isSuccess()) {
            throw new Exception(join(', ', $result->getErrorMessages()));
        }

        Logger::log("Обновлен HL {$table_name}", Logger::COLOR_GREEN);
    }

    /**
     * Удалить HL.
     *
     * @param string $table_name
     *
     * @throws Exception
     */
    public static function delete(string $table_name) : void
    {
        $id = Helpers::getHlId($table_name);
        $result = HighloadBlockTable::delete($id);

        if (!$result->isSuccess()) {
            throw new Exception(join(', ', $result->getErrorMessages()));
        }

        Logger::log("Удален HL {$table_name}", Logger::COLOR_GREEN);
    }

    /**
     * Установить настройки для добавления HL по умолчанию.
     *
     * @param string $name       Название highload-блока
     * @param string $table_name Название таблицы с элементами highload-блока.
     *
     * @return $this
     */
    public function constructDefault(string $name, string $table_name) : self
    {
        return $this->setName($name)->setTableName($table_name);
    }

    /**
     * Название highload-блока.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->fields['NAME'] = $name;

        return $this;
    }

    /**
     * Название таблицы с элементами highload-блока.
     *
     * @param string $table_name
     * @return $this
     */
    public function setTableName(string $table_name) : self
    {
        $this->fields['TABLE_NAME'] = $table_name;

        return $this;
    }

    /**
     * Установить локализацию.
     *
     * @param string $lang
     * @param string $text
     *
     * @return $this
     */
    public function setLang(string $lang, string $text) : self
    {
        $this->lang[$lang] = $text;

        return $this;
    }
}
