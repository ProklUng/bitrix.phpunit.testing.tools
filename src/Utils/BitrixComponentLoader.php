<?php

namespace Prokl\BitrixTestingTools\Utils;

use Exception;
use Prokl\BitrixTestingTools\Invokers\ComponentInvoker;
use ReflectionException;

/**
 * Class BitrixComponentLoader
 * @package Prokl\BitrixTestingTools\Utils
 *
 * @since 07.12.2020
 */
class BitrixComponentLoader
{
    /**
     * @var ComponentInvoker $componentInvoker Исполнитель компонентов.
     */
    private $componentInvoker;

    /**
     * BitrixComponentLoader constructor.
     *
     * @param ComponentInvoker $componentInvoker Исполнитель компонентов.
     */
    public function __construct(
        ComponentInvoker $componentInvoker
    ) {
        $this->componentInvoker = $componentInvoker;
    }

    /**
     * @param string      $component Название компонента.
     * @param array       $arParams  Параметры компонента.
     * @param string|null $template  Шаблон компонента.
     *
     * @return mixed  Если $template = null, то вернет html, сгенерированный компонентом.
     * Иначе - куцее (полученное в result_modifier) содержание $arResult (ограничение Битрикса).
     *
     * @throws ReflectionException | Exception Когда что-то пошло не так.
     */
    public function load(string $component, array $arParams = [], string $template = null)
    {
        $this->componentInvoker->setName($component)
                               ->setTemplate($template)
                               ->setParams($arParams);

        $this->componentInvoker->init();

        if ($template) {
            ob_start();
            $this->componentInvoker->execute();

            return ob_get_clean();
        }

        $this->componentInvoker->execute();

        return $this->componentInvoker->getArResult();
    }
}