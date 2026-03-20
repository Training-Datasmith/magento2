<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Db;

use Magento\Framework\Object_Manager_Interface;
/**
 * @api
 * @since 100.0.2
 */
class Backup_Factory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var string
     */
    private $backup_instance_name;
    /**
     * @var string
     */
    private $backup_db_instance_name;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $backupInstanceName
     * @param string $backupDbInstanceName
     */
    public function __construct(Object_Manager_Interface $object_manager, $backup_instance_name, $backup_db_instance_name)
    {
        $this->object_manager = $object_manager;
        $this->backup_instance_name = $backup_instance_name;
        $this->backup_db_instance_name = $backup_db_instance_name;
    }
    /**
     * Create backup model
     *
     * @param array $arguments
     * @return \Magento\Framework\Backup\Db\BackupInterface
     */
    public function create_backup_model(array $arguments = [])
    {
        return $this->object_manager->create($this->backup_instance_name, $arguments);
    }
    /**
     * Create backup Db model
     *
     * @param array $arguments
     * @return \Magento\Framework\Backup\Db\BackupDbInterface
     */
    public function create_backup_db_model(array $arguments = [])
    {
        return $this->object_manager->create($this->backup_db_instance_name, $arguments);
    }
}