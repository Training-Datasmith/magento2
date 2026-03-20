<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 100.0.2
 */
class Denied extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Model\Auth\Session $auth_session, array $data = [])
    {
        $this->_auth_session = $auth_session;
        parent::__construct($context, $data);
    }
    /**
     * @return bool
     */
    public function has_available_resources()
    {
        $user = $this->_auth_session->get_user();
        if ($user && $user->get_has_available_resources()) {
            return true;
        }
        return false;
    }
}