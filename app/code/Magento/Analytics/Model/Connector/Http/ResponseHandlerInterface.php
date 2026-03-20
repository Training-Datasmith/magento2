<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 */
interface Response_Handler_Interface
{
    /**
     * Process response body
     *
     * @return bool|string
     */
    public function handle_response(array $response_body);
}