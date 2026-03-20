<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backup\Helper\Data as Helper;
use Magento\Framework\App\Object_Manager;
/**
 * Backup admin controller
 *
 * @phpcs:ignore Magento2.Classes.AbstractApi.AbstractApi
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backup::backup';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backup_factory;
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_file_factory;
    /**
     * @var \Magento\Backup\Model\BackupFactory
     */
    protected $_backup_model_factory;
    /**
     * @var \Magento\Framework\App\MaintenanceMode
     */
    protected $maintenance_mode;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backup\Model\BackupFactory $backupModelFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     * @param Helper|null $helper
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $core_registry, \Magento\Framework\Backup\Factory $backup_factory, \Magento\Framework\App\Response\Http\File_Factory $file_factory, \Magento\Backup\Model\Backup_Factory $backup_model_factory, \Magento\Framework\App\Maintenance_Mode $maintenance_mode, ?Helper $helper = null)
    {
        $this->_core_registry = $core_registry;
        $this->_backup_factory = $backup_factory;
        $this->_file_factory = $file_factory;
        $this->_backup_model_factory = $backup_model_factory;
        $this->maintenance_mode = $maintenance_mode;
        $this->helper = $helper ?? Object_Manager::get_instance()->get(Helper::class);
        parent::__construct($context);
    }
    /**
     * @inheritDoc
     * @since 100.2.6
     */
    public function dispatch(\Magento\Framework\App\Request_Interface $request)
    {
        if (!$this->helper->is_enabled()) {
            return $this->_redirect('*/*/disabled');
        }
        return parent::dispatch($request);
    }
}