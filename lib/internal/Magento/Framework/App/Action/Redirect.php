<?php

declare (strict_types=1);
/**
 * Redirect action class
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\Response_Interface;
/**
 * Issue a redirect.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Redirect extends Abstract_Action
{
    /**
     * Redirect response
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dispatch(Request_Interface $request)
    {
        return $this->execute();
    }
    /**
     * @return ResponseInterface
     */
    public function execute()
    {
        return $this->_response;
    }
}