<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

use CIBlock;

class OnBeforeIBlockDelete extends BaseHandler implements HandlerInterface
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->fields = $this->getIBlockById($params[0]);
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName()
    {
        return "auto_delete_iblock_{$this->fields['CODE']}";
    }

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'auto_delete_iblock';
    }

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace()
    {
        return [
            'code' => "'".$this->fields['CODE']."'",
        ];
    }

    /**
     * Get iblock by id without checking permissions.
     *
     * @param $id
     *
     * @return array
     */
    protected function getIBlockById($id)
    {
        $filter = [
            'ID'                => $id,
            'CHECK_PERMISSIONS' => 'N',
        ];

        return (new CIBlock())->getList([], $filter)->fetch();
    }
}
