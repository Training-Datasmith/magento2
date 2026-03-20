<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\Csrf_Aware_Action_Interface;
use Magento\Framework\App\Request\Invalid_Request_Exception;
use Magento\Framework\App\Request_Interface;
/**
 * Forward request further.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Forward extends Abstract_Action implements Csrf_Aware_Action_Interface
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dispatch(Request_Interface $request)
    {
        return $this->execute();
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_request->set_dispatched(false);
        return $this->_response;
    }
    /**
     * @inheritDoc
     */
    public function create_csrf_validation_exception(Request_Interface $request): ?Invalid_Request_Exception
    {
        return new Invalid_Request_Exception($this->_response);
    }
    /**
     * @inheritDoc
     */
    public function validate_for_csrf(Request_Interface $request): ?bool
    {
        // This exists so that we can forward to the noroute action in the admin
        return true;
    }
}