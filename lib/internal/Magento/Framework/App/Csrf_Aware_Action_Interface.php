<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Request\Invalid_Request_Exception;
/**
 * Action that's aware of CSRF protection.
 *
 * @api
 */
interface Csrf_Aware_Action_Interface extends Action_Interface
{
    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function create_csrf_validation_exception(Request_Interface $request): ?Invalid_Request_Exception;
    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validate_for_csrf(Request_Interface $request): ?bool;
}