<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGet;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPost;
class Index extends \Magento\Backend\Controller\Adminhtml\Index implements Http_Get, Http_Post
{
    /**
     * Admin area entry point
     * Always redirects to the startup page url
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path($this->_backend_url->get_startup_page_url());
    }
}