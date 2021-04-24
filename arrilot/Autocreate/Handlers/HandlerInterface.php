<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate\Handlers;

interface HandlerInterface
{
    /**
     * Get migration name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get template name.
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get array of placeholders to replace.
     *
     * @return array
     */
    public function getReplace();
}
