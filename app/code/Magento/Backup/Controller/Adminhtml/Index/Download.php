<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
class Download extends \Magento\Backup\Controller\Adminhtml\Index implements Http_Get_Action_Interface
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $result_raw_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backup\Model\BackupFactory $backupModelFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $core_registry, \Magento\Framework\Backup\Factory $backup_factory, \Magento\Framework\App\Response\Http\File_Factory $file_factory, \Magento\Backup\Model\Backup_Factory $backup_model_factory, \Magento\Framework\App\Maintenance_Mode $maintenance_mode, \Magento\Framework\Controller\Result\Raw_Factory $result_raw_factory)
    {
        parent::__construct($context, $core_registry, $backup_factory, $file_factory, $backup_model_factory, $maintenance_mode);
        $this->result_raw_factory = $result_raw_factory;
    }
    /**
     * Download backup action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function execute()
    {
        /* @var $backup \Magento\Backup\Model\Backup */
        $backup = $this->_backup_model_factory->create($this->get_request()->get_param('time'), $this->get_request()->get_param('type'));
        if (!$backup->get_time() || !$backup->exists()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $result_redirect = $this->result_redirect_factory->create();
            $result_redirect->set_path('backup/*');
            return $result_redirect;
        }
        $file_name = $this->_object_manager->get(\Magento\Backup\Helper\Data::class)->generate_backup_download_name($backup);
        return $this->_file_factory->create($file_name, ['type' => 'filename', 'value' => $backup->get_path() . DIRECTORY_SEPARATOR . $backup->get_file_name()], Directory_List::VAR_DIR, 'application/octet-stream', $backup->get_size());
    }
}