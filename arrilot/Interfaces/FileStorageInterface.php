<?php

namespace Arrilot\BitrixMigrationsFork\Interfaces;

interface FileStorageInterface
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function getMigrationFiles($path);

    /**
     * Require a file.
     *
     * @param $path
     *
     * @return void
     */
    public function requireFile($path);

    /**
     * Create a directory if it does not exist.
     *
     * @param $dir
     *
     * @return void
     */
    public function createDirIfItDoesNotExist($dir);

    /**
     * Get the content of a file.
     *
     * @param string $path
     *
     * @return string
     */
    public function getContent($path);

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param bool   $lock
     *
     * @return int
     */
    public function putContent($path, $contents, $lock = false);

    /**
     * Check if file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path);

    /**
     * Delete file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path);
}
