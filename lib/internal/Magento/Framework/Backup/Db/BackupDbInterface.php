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
interface Backup_Db_Interface
{
    /**
     * Create DB backup
     *
     * @param BackupInterface $backup
     * @return void
     */
    public function create_backup(\Magento\Framework\Backup\Db\Backup_Interface $backup);
    /**
     * Get database backup size
     *
     * @return int
     */
    public function get_db_backup_size();
}