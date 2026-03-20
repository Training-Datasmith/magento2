<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
/**
 * Interface ExceptionHandler
 *
 * @api
 */
interface Exception_Handler_Interface
{
    /**
     * Handles exception of HTTP web application
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    public function handle(Bootstrap $bootstrap, \Exception $exception, Response_Http $response, Request_Http $request): bool;
}