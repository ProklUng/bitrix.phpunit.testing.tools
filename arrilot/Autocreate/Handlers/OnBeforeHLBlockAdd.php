<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use Arrilot\BitrixMigrationsFork\Exceptions\SkipHandlerException;
use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockAdd extends BaseHandler implements HandlerInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * Constructor.
     *
     * @param array $params
     *
     * @throws SkipHandlerException
     */
    public function __construct($params)
    {
        $this->event = $params[0];

        $this->fields = $this->event->getParameter('fields');
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return 'auto_add_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_add_hlblock';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace()
    {
        return [
            'fields' => var_export($this->fields, true),
        ];
    }
}
