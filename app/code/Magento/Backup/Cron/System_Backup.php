<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Cron;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Store\Model\Scope_Interface;
/**
 * Performs scheduled backup.
 */
class System_Backup
{
    public const XML_PATH_BACKUP_ENABLED = 'system/backup/enabled';
    public const XML_PATH_BACKUP_TYPE = 'system/backup/type';
    public const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';
    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = [];
    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backup_data = null;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scope_config;
    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backup_factory;
    /**
     * @var \Magento\Framework\App\MaintenanceMode
     */
    protected $maintenance_mode;
    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(\Magento\Backup\Helper\Data $backup_data, \Magento\Framework\Registry $core_registry, \Psr\Log\Logger_Interface $logger, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Backup\Factory $backup_factory, \Magento\Framework\App\Maintenance_Mode $maintenance_mode)
    {
        $this->_backup_data = $backup_data;
        $this->_core_registry = $core_registry;
        $this->_logger = $logger;
        $this->_scope_config = $scope_config;
        $this->_filesystem = $filesystem;
        $this->_backup_factory = $backup_factory;
        $this->maintenance_mode = $maintenance_mode;
    }
    /**
     * Create Backup
     *
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->_backup_data->is_enabled()) {
            return $this;
        }
        if (!$this->_scope_config->is_set_flag(self::XML_PATH_BACKUP_ENABLED, Scope_Interface::SCOPE_STORE)) {
            return $this;
        }
        if ($this->_scope_config->is_set_flag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, Scope_Interface::SCOPE_STORE)) {
            $this->maintenance_mode->set(true);
        }
        $type = $this->_scope_config->get_value(self::XML_PATH_BACKUP_TYPE, Scope_Interface::SCOPE_STORE);
        $this->_errors = [];
        try {
            $backup_manager = $this->_backup_factory->create($type)->set_backup_extension($this->_backup_data->get_extension_by_type($type))->set_time(time())->set_backups_dir($this->_backup_data->get_backups_dir());
            $this->_core_registry->register('backup_manager', $backup_manager);
            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                $backup_manager->set_root_dir($this->_filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path())->add_ignore_paths($this->_backup_data->get_backup_ignore_paths());
            }
            $backup_manager->create();
            $message = $this->_backup_data->get_create_success_message_by_type($type);
            $this->_logger->info($message);
        } catch (\Exception $e) {
            $this->_errors[] = $e->get_message();
            $this->_errors[] = $e->get_trace();
            throw $e;
        }
        if ($this->_scope_config->is_set_flag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, Scope_Interface::SCOPE_STORE)) {
            $this->maintenance_mode->set(false);
        }
        return $this;
    }
}