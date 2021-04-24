<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use CIBlockProperty;

class OnBeforeIBlockPropertyDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->fields = CIBlockProperty::getByID($params[0])->fetch();
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return "auto_delete_iblock_element_property_{$this->fields['CODE']}_in_ib_{$this->fields['IBLOCK_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_delete_iblock_element_property';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace()
    {
        return [
            'iblockId' => $this->fields['IBLOCK_ID'],
            'code'     => "'".$this->fields['CODE']."'",
        ];
    }
}
