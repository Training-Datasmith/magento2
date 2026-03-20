<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Validate interface before giving passing it to an ActionInterface.
 *
 * @api
 */
interface Validator_Interface
{
    /**
     * Validate request and throw the exception if it's invalid.
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @throws InvalidRequestException If request was invalid.
     *
     * @return void
     */
    public function validate(Request_Interface $request, Action_Interface $action): void;
}