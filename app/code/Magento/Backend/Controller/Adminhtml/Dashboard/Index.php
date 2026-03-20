<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\Controller\Adminhtml\Dashboard as DashboardAction;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
class Index extends Dashboard_Action implements Http_Get_Action_Interface, Http_Post_Action_Interface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\Page_Factory $result_page_factory)
    {
        parent::__construct($context);
        $this->result_page_factory = $result_page_factory;
    }
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_active_menu('Magento_Backend::dashboard');
        $result_page->add_breadcrumb(__('Dashboard'), __('Dashboard'));
        $result_page->get_config()->get_title()->prepend(__('Dashboard'));
        return $result_page;
    }
}