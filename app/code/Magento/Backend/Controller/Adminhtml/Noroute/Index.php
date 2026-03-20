<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Noroute;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Backend\App\Action
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
     * Noroute action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_status_header(404, '1.1', 'Not Found');
        $result_page->set_header('Status', '404 File not found');
        $result_page->add_handle('adminhtml_noroute');
        return $result_page;
    }
    /**
     * Error page should be public accessible. Do not check keys to avoid redirect loop
     *
     * @return bool
     */
    protected function _validate_secret_key()
    {
        return true;
    }
}