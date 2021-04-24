<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use Arrilot\BitrixMigrationsFork\Exceptions\SkipHandlerException;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockDelete extends BaseHandler implements HandlerInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * HLBlock id.
     *
     * @var int
     */
    protected $id;

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

        $eventParams = $this->event->getParameters();

        $this->id = $eventParams['id']['ID'];
        $this->fields = HighloadBlockTable::getById($this->id)->fetch();
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return 'auto_delete_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_delete_hlblock';
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
            'id'     => $this->id,
        ];
    }
}
