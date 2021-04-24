<?php

namespace Arrilot\BitrixMigrationsFork\Autocreate;

use CAdminNotify;

class Notifier
{
    /**
     * Notification tag.
     *
     * @var string
     */
    protected $tag = 'arrilot_new_migration';

    /**
     * Show notification that migration has been created.
     *
     * @param $migration
     */
    public function newMigration($migration)
    {
        $notification = [
            'MESSAGE'      => 'Migration <strong>'.$migration.'</strong> has been created and applied.',
            'TAG'          => $this->tag,
            'MODULE_ID'    => 'main',
            'ENABLE_CLOSE' => 'Y',
        ];

        CAdminNotify::add($notification);
    }

    /**
     * Delete notification from the previous migration.
     */
    public function deleteNotificationFromPreviousMigration()
    {
        if (defined('ADMIN_SECTION')) {
            CAdminNotify::deleteByTag($this->tag);
        }
    }
}
