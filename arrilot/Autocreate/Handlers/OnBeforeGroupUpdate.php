<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use Arrilot\BitrixMigrationsFork\Exceptions\StopHandlerException;

class OnBeforeGroupUpdate extends BaseHandler implements HandlerInterface
{
    /**
     * Bitrix group id.
     *
     * @var int
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param array $params
     *
     * @throws StopHandlerException
     */
    public function __construct($params)
    {
        $this->id = $params[0];
        $this->fields = $params[1];

        if (!$this->fields['STRING_ID']) {
            throw new StopHandlerException('Code is required to create a migration');
        }
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return "auto_update_group_{$this->fields['STRING_ID']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_update_group';
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
