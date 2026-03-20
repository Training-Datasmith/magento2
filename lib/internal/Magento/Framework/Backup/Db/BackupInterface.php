<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Db;

/**
 * @api
 *
 * @deprecated 101.0.7 Backups should be done using other means.
 * @since 100.0.2
 */
interface Backup_Interface
{
    /**
     * Set backup time
     *
     * @param int $time
     * @return $this
     */
    public function set_time($time);
    /**
     * Set backup type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type);
    /**
     * Set backup path
     *
     * @param string $path
     * @return $this
     */
    public function set_path($path);
    /**
     * Set backup name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return $this
     */
    public function open($write = false);
    /**
     * Write to backup file
     *
     * @param string $data
     * @return $this
     */
    public function write($data);
    /**
     * Close open backup file
     *
     * @return $this
     */
    public function close();
}