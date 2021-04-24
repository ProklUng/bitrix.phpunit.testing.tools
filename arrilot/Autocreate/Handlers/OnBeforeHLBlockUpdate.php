<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use Arrilot\BitrixMigrationsFork\Exceptions\SkipHandlerException;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Event;

class OnBeforeHLBlockUpdate extends BaseHandler implements HandlerInterface
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

        $this->fields = $eventParams['fields'];
        $this->id = $eventParams['id']['ID'];

        if (!$this->fieldsHaveBeenChanged()) {
            throw new SkipHandlerException();
        }
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return 'auto_update_hlblock_'.$this->fields['TABLE_NAME'];
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_update_hlblock';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace()
    {
        return [
            'id'     => $this->id,
            'fields' => var_export($this->fields, true),
        ];
    }

    /**
     * Determine if fields have been changed.
     *
     * @return bool
     */
    protected function fieldsHaveBeenChanged()
    {
        $old = HighloadBlockTable::getById($this->id)->fetch();
        $new = $this->fields + ['ID' => (string) $this->id];

        return $new != $old;
    }
}
