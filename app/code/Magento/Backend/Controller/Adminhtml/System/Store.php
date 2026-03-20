<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
/**
 * Store controller
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class Store extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::store';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry;
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter_manager;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $result_forward_factory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $core_registry, \Magento\Framework\Filter\Filter_Manager $filter_manager, \Magento\Backend\Model\View\Result\Forward_Factory $result_forward_factory, \Magento\Framework\View\Result\Page_Factory $result_page_factory)
    {
        $this->_core_registry = $core_registry;
        $this->filter_manager = $filter_manager;
        parent::__construct($context);
        $this->result_forward_factory = $result_forward_factory;
        $this->result_page_factory = $result_page_factory;
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function create_page()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_active_menu('Magento_Backend::system_store')->add_breadcrumb(__('System'), __('System'))->add_breadcrumb(__('Manage Stores'), __('Manage Stores'));
        return $result_page;
    }
    /**
     * Backup database
     *
     * @return bool
     *
     * @deprecated 100.2.7 Backup module is to be removed.
     * @see Nothing
     */
    protected function _backup_database()
    {
        if (!$this->get_request()->get_param('create_backup')) {
            return true;
        }
        try {
            /** @var \Magento\Backup\Model\Db $backupDb */
            $backup_db = $this->_object_manager->create(\Magento\Backup\Model\Db::class);
            /** @var \Magento\Backup\Model\Backup $backup */
            $backup = $this->_object_manager->create(\Magento\Backup\Model\Backup::class);
            /** @var Filesystem $filesystem */
            $filesystem = $this->_object_manager->get(\Magento\Framework\Filesystem::class);
            $backup->set_time(time())->set_type('db')->set_path($filesystem->get_directory_read(Directory_List::VAR_DIR)->get_absolute_path('backups'));
            $backup_db->create_backup($backup);
            $this->message_manager->add_success_message(__('The database was backed up.'));
        } catch (\Magento\Framework\Exception\Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
            return false;
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('We can\'t create a backup right now. Please try again later.'));
            return false;
        }
        return true;
    }
    /**
     * Add notification on deleting store / store view / website
     *
     * @param string $typeTitle
     * @return $this
     */
    protected function _add_deletion_notice($type_title)
    {
        $this->message_manager->add_notice_message(__('Deleting a %1 will not delete the information associated with the %1 (e.g. categories, products, etc.)' . ', but the %1 will not be able to be restored. It is suggested that you create a database backup ' . 'before deleting the %1.', $type_title));
        return $this;
    }
}