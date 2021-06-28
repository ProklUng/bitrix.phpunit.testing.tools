<?php

namespace Arrilot\BitrixMigrationsFork\Interfaces;

interface MigrationInterface
{
    /**
     * Run the migration.
     *
     * @return mixed
     */
    public function up();

    /**
     * Reverse the migration.
     *
     * @return mixed
     */
    public function down();

    /**
     * use transaction
     *
     * @param boolean $default Default value.
     *
     * @return boolean
     */
    public function useTransaction($default = false);
}
