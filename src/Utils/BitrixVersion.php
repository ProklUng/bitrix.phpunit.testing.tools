<?php

namespace Prokl\BitrixTestingTools\Utils;

use Bitrix\Main\ModuleManager;

/**
 * Class BitrixVersion
 * @package Prokl\BitrixTestingTools\Utils
 *
 * @since 07.12.2020
 */
class BitrixVersion
{
    /**
     * @var array $moduleVersion
     */
    private $moduleVersion;

    /**
     * Возвращает версию главного модуля.
     *
     * @return string
     */
    public function getProductVersion(): string
    {
        return $this->getModuleVersion('main');
    }

    /**
     * @param string $module Название модуля.
     *
     * @return mixed
     */
    public function getModuleVersion(string $module)
    {
        $res = ModuleManager::getVersion($module);
        $this->moduleVersion[$module] = is_string($res) ? $res : 'undefined';

        return $this->moduleVersion[$module];
    }
}