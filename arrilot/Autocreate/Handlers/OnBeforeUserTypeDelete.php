<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use CUserTypeEntity;

class OnBeforeUserTypeDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->fields = is_array($params[0]) ? $params[0] : CUserTypeEntity::getByID($params[0]);
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return "auto_delete_uf_{$this->fields['FIELD_NAME']}_from_entity_{$this->fields['ENTITY_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_delete_uf';
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
            'code'     => "'".$this->fields['FIELD_NAME']."'",
            'entity'   => "'".$this->fields['ENTITY_ID']."'",
            'fields'   => var_export($this->fields, true),
        ];
    }
}
