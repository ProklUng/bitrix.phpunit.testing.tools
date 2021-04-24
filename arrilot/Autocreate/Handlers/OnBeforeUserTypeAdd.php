<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

class OnBeforeUserTypeAdd extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->fields = $params[0];
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return "auto_add_uf_{$this->fields['FIELD_NAME']}_to_entity_{$this->fields['ENTITY_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_add_uf';
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
            'code'   => "'".$this->fields['FIELD_NAME']."'",
            'entity' => "'".$this->fields['ENTITY_ID']."'",
        ];
    }
}
