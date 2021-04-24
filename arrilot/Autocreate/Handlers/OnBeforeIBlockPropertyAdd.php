<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use Arrilot\BitrixMigrationsFork\Exceptions\SkipHandlerException;

class OnBeforeIBlockPropertyAdd extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @param array $params
     * @throws SkipHandlerException
     */
    public function __construct($params)
    {
        $this->fields = $params[0];

        if (!$this->fields['IBLOCK_ID']) {
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
        return "auto_add_iblock_element_property_{$this->fields['CODE']}_to_ib_{$this->fields['IBLOCK_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_add_iblock_element_property';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace()
    {
        return [
            'fields'   => var_export($this->fields, true),
            'iblockId' => $this->fields['IBLOCK_ID'],
            'code'     => "'".$this->fields['CODE']."'",
        ];
    }
}
