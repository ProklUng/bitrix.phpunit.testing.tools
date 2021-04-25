<?php

namespace Prokl\BitrixTestingTools\Traits;

use Prokl\TestingTools\Tools\PHPUnitUtils;
use ReflectionException;

/**
 * Trait BBCComponentTrait
 * Утилиты для тестирования Базовых битриксовых компонентов (https://github.com/bitrix-expert/bbc).
 * @package Local\Tests\Traits
 */
trait BBCComponentTrait
{
    /**
     * Задать arParams компонента.
     *
     * @param array $arParams
     *
     * @return void
     * @throws ReflectionException
     */
    private function arParams(array $arParams = []) : void
    {
        PHPUnitUtils::setProtectedProperty(
            $this->obTestObject,
            'arParams',
            $arParams
        );
    }

    /**
     * Выполнить executeMain.
     *
     * @param mixed $mock
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function runExecuteMain($mock = null)
    {
        $mock = $mock ?: $this->obTestObject;

        PHPUnitUtils::callMethod(
            $mock,
            'executeMain',
            []
        );

        return PHPUnitUtils::getProtectedProperty(
            $mock,
            'arResult'
        );
    }
}
