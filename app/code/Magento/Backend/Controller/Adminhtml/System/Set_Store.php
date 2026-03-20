<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

class Set_Store extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @return void
     */
    public function execute()
    {
        $store_id = (int) $this->get_request()->get_param('store');
        if ($store_id) {
            $this->_session->set_store_id($store_id);
        }
        $this->get_response()->set_redirect($this->_redirect->get_redirect_url($this->get_url('*')));
    }
}