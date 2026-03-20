<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

class Denied_Iframe extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $result_raw_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\Raw_Factory $result_raw_factory)
    {
        parent::__construct($context);
        $this->result_raw_factory = $result_raw_factory;
    }
    /**
     * Retrieve response for deniedIframeAction()
     *
     * @return string
     */
    protected function _get_denied_iframe()
    {
        return '<script>parent.window.location = \'' . $this->_helper->get_home_page_url() . '\';</script>';
    }
    /**
     * Denied IFrame action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $result_raw = $this->result_raw_factory->create();
        return $result_raw->set_contents($this->_get_denied_iframe());
    }
}