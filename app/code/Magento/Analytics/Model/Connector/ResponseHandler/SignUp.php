<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Response_Handler;

use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Connector\Http\Response_Handler_Interface;
/**
 * Stores access token to MBI that received in body.
 */
class Sign_Up implements Response_Handler_Interface
{
    public function __construct(private readonly Analytics_Token $analytics_token)
    {
    }
    /**
     * @inheritdoc
     */
    public function handle_response(array $body)
    {
        if (isset($body['access-token']) && !empty($body['access-token'])) {
            $this->analytics_token->store_token($body['access-token']);
            return $body['access-token'];
        }
        return false;
    }
}