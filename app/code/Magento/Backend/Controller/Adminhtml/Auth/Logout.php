<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGet;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPost;
class Logout extends \Magento\Backend\Controller\Adminhtml\Auth implements Http_Get, Http_Post
{
    /**
     * Administrator logout action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_auth->logout();
        $this->message_manager->add_success_message(__('You have logged out.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path($this->_helper->get_home_page_url());
    }
}