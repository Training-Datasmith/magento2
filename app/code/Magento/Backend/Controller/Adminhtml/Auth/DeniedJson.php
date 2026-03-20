<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Denied_Json extends \Magento\Backend\Controller\Adminhtml\Auth implements Http_Get_Action_Interface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $result_json_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\Json_Factory $result_json_factory)
    {
        parent::__construct($context);
        $this->result_json_factory = $result_json_factory;
    }
    /**
     * Retrieve response for deniedJsonAction()
     *
     * @return array
     */
    protected function _get_denied_json()
    {
        return ['ajaxExpired' => 1, 'ajaxRedirect' => $this->_helper->get_home_page_url()];
    }
    /**
     * Denied JSON action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $result_json = $this->result_json_factory->create();
        return $result_json->set_data($this->_get_denied_json());
    }
}