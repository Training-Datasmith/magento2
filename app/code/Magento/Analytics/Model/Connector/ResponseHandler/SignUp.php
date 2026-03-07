<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;

/**
 * Stores access token to MBI that received in body.
 */
class SignUp implements ResponseHandlerInterface
{
    public function __construct(private readonly AnalyticsToken $analyticsToken)
    {
    }

    /**
     * @inheritdoc
     */
    public function handleResponse(array $body)
    {
        if (isset($body['access-token']) && !empty($body['access-token'])) {
            $this->analyticsToken->storeToken($body['access-token']);
            return $body['access-token'];
        }

        return false;
    }
}
