<?php

namespace Arrilot\BitrixMigrationsFork\Traits;

use Arrilot\BitrixMigrationsFork\BaseMigrations\BitrixMigration;

/**
 * @mixin BitrixMigration
 */
trait PropertiesTrait
{
    protected function addProperties()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $id = $this->getIblockIdByCode($this->getIblockCode());

        foreach ($this->getProperties() as $code => $field) {
            $field['IBLOCK_ID'] = $id;
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->addIblockElementProperty($field);
        }
    }

    protected function removeProperties()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $id = $this->getIblockIdByCode($this->getIblockCode());

        foreach (array_keys($this->getProperties()) as $code) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->deleteIblockElementPropertyByCode($id, $code);
        }
    }

    /**
     * Returns array of property declarations with property codes as keys
     * @return array
     */
    abstract protected function getProperties();

    /**
     * @return string
     */
    abstract protected function getIblockCode();
}