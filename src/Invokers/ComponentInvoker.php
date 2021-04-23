<?php

namespace Prokl\BitrixTestingTools\Invokers;

use CBitrixComponent;
use Exception;
use ReflectionException;

/**
 * ComponentInvoker
 * @package Local\Bundles\BitrixToolsBundle\Service\Invokers
 *
 * @since 07.12.2020 Рефакторинг под сервис.
 */
class ComponentInvoker extends BaseInvoker
{
    /**
     * @var CBitrixComponent $bitrixComponent Объект компонента.
     */
    private $bitrixComponent;

    /**
     * @var CBitrixComponent $runComponent
     */
    private $runComponent;

    /**
     * @var string $name Название компонента.
     */
    private $name;

    /**
     * @var array $params Параметры.
     */
    private $params = [];

    /**
     * @var string $path Путь.
     */
    private $path;

    /**
     * @var mixed $executeResult Результат исполнения компонента.
     */
    private $executeResult;

    /** @var mixed|string $template Шаблон компонента. */
    private $template;

    /**
     * ComponentInvoker constructor.
     *
     * @param CBitrixComponent $bitrixComponent Компонент.
     */
    public function __construct(CBitrixComponent $bitrixComponent)
    {
        $this->path = Module::getBitrixPath();
        $this->bitrixComponent = $bitrixComponent;
    }

    /**
     * Инициализация.
     *
     * @return void
     */
    public function init() : void
    {
        $this->bitrixComponent->__name = $this->name;
        $this->bitrixComponent->initComponent($this->name, $this->template);
    }

    /**
     * Исполнить компонент.
     *
     * @return void
     * @throws ReflectionException
     */
    public function execute() : void
    {
        $classOfComponent = $this->getObjectPropertyValue($this->bitrixComponent, 'classOfComponent');

        if ($classOfComponent) {
            /** @var CBitrixComponent $component  */
            $component = new $classOfComponent($this);
            $component->arParams = $component->onPrepareComponentParams($this->params);
            $this->invokeMethod($component, '__prepareComponentParams', $component->arParams);
            $component->onIncludeComponentLang();
        } else {
            $this->invokeMethod($this->bitrixComponent, '__prepareComponentParams', $this->params);
            $this->bitrixComponent->arParams = $this->params;
            $this->bitrixComponent->includeComponentLang();
        }

        // execute.
        if ($this->template === null) {
            ob_start();
        }

        $this->executeResult = $this->bitrixComponent->executeComponent();
        if ($this->template === null) {
            ob_get_clean();
        }

        $this->runComponent = $classOfComponent ? $component : $this->bitrixComponent;
    }

    /**
     * @param string $name Название компонента.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed|string $template Шаблон компонента.
     *
     * @return $this
     */
    public function setTemplate($template) : self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param array $arParams Параметры компонента.
     *
     * @return void
     */
    public function setParams(array $arParams) : void
    {
        $this->params = $arParams;
    }

    /**
     * @param string $name Ключ в $arResult после исполнения компонента.
     *
     * @return mixed
     * @throws Exception
     */
    public function getResultValue(string $name)
    {
        $this->throwIfWasntExecute();

        return $this->runComponent->arResult[$name];
    }

    /**
     * Неполноценный $arResult (как минимум, в случае со старыми компонентами). Полный $arResult существует только
     * в контексте функции __includeComponent.
     *
     * @return array
     * @throws Exception
     */
    public function getArResult() : array
    {
        $this->throwIfWasntExecute();

        return $this->runComponent->arResult;
    }

    /**
     * Результат исполнения.
     *
     * @return mixed
     */
    public function getExecuteResult()
    {
        return $this->executeResult;
    }

    /**
     * @return CBitrixComponent
     */
    public function getRunComponent(): CBitrixComponent
    {
        return $this->runComponent;
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function throwIfWasntExecute() : void
    {
        if ($this->runComponent !== null) {
            return;
        }

        throw new Exception('Execute of invoker has not been yet.');
    }
}
