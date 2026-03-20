<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Backend_App;

use Magento\Backend\App\Abstract_Action;
/**
 * Controller which handles authentication of backend app and redirects back to set cookie with backend app path
 */
class Redirect extends Abstract_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_public_actions = ['redirect'];
    /**
     * @var \Magento\Backend\App\BackendAppList|null
     */
    private $backend_app_list;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Backend\App\Backend_App_List $backend_app_list)
    {
        parent::__construct($context);
        $this->backend_app_list = $backend_app_list;
    }
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result_redirect = $this->result_redirect_factory->create();
        if ($this->get_request()->get_param('app')) {
            $url = $this->get_url('*/*/*', []) . '?app=' . $this->get_request()->get_param('app');
            return $result_redirect->set_url($url);
        }
        return $result_redirect->set_url($this->get_url('*/index/index'));
    }
    /**
     * @return bool
     */
    protected function _is_allowed()
    {
        $backend_app = $this->backend_app_list->get_backend_app($this->get_request()->get_param('app'));
        if ($backend_app) {
            return $this->_authorization->is_allowed($backend_app->get_acl_resource());
        }
        return true;
    }
}