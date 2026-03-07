<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 */
interface ResponseHandlerInterface
{
    /**
     * Process response body
     *
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
