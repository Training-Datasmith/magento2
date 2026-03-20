<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup;

/**
 * @api
 *
 * @deprecated 101.0.7 Backups should be done using other means.
 * @since 100.0.2
 */
interface Backup_Interface
{
    /**
     * Create Backup
     *
     * @return boolean
     */
    public function create();
    /**
     * Rollback Backup
     *
     * @return boolean
     */
    public function rollback();
    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     */
    public function set_backup_extension($backup_extension);
    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     */
    public function set_resource_model($resource_model);
    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     */
    public function set_time($time);
    /**
     * Get Backup Type
     *
     * @return string
     */
    public function get_type();
    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     */
    public function set_backups_dir($backups_dir);
}