<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

class Denied extends \Magento\Backend\App\Action
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->_auth->is_logged_in()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $result_redirect = $this->result_redirect_factory->create();
            $result_redirect->set_status_header(403, '1.1', 'Forbidden');
            return $result_redirect->set_path('*/auth/login');
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_status_header(403, '1.1', 'Forbidden');
        $result_page->add_handle('adminhtml_denied');
        return $result_page;
    }
}