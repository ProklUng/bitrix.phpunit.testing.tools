<?php

namespace Prokl\BitrixTestingTools\Invokers;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

/**
 * Class EventInvoker
 * @package Prokl\BitrixTestingTools\Invokers
 */
class EventInvoker extends BaseInvoker
{
    /**
     * @var Event $event
     */
    private $event;

    /**
     * EventInvoker constructor.
     *
     * @param string $module    Модуль.
     * @param string $eventName Название события.
     */
    public function __construct(string $module, string $eventName)
    {
        $this->event = new Event($module, $eventName);
    }

    /**
     * @param array $listParams Параметры.
     *
     * @return void
     * @throws ArgumentTypeException
     */
    public function setExecuteParams(array $listParams) : void
    {
        $this->event->setParameters($listParams);
    }

    /**
     * @return void
     */
    public function execute() : void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->send($this->event);
    }

    /**
     * @return integer
     */
    public function countOfHandlers() : int
    {
        $eventManager = EventManager::getInstance();

        return count($eventManager->findEventHandlers($this->event->getModuleId(), $this->event->getEventType()));
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
