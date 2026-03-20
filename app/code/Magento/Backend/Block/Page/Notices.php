<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Adminhtml header notices block
 */
namespace Magento\Backend\Block\Page;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Backend\Block\Template
{
    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     */
    public function display_noscript_notice()
    {
        return $this->_scope_config->get_value('web/browser_capabilities/javascript', \Magento\Store\Model\Scope_Interface::SCOPE_STORE);
    }
    /**
     * Check if demo store notice should be displayed
     *
     * @return boolean
     */
    public function display_demo_notice()
    {
        return $this->_scope_config->get_value('design/head/demonotice', \Magento\Store\Model\Scope_Interface::SCOPE_STORE);
    }
}