<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\Page_Factory;
class Index extends Action implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Logging::system_magento_logging_bulk_operations';
    /**
     * @param string $menuId
     */
    public function __construct(Context $context, private readonly Page_Factory $result_page_factory, private $menu_id = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations')
    {
        parent::__construct($context);
    }
    /**
     * Bulk list action
     *
     * @return Page
     */
    public function execute()
    {
        $result_page = $this->result_page_factory->create();
        $result_page->init_layout();
        $this->_set_active_menu($this->menu_id);
        $result_page->get_config()->get_title()->prepend(__('Bulk Actions Log'));
        return $result_page;
    }
}