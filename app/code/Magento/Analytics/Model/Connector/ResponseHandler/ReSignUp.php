<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Response_Handler;

use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Analytics\Model\Connector\Http\Response_Handler_Interface;
use Magento\Analytics\Model\Subscription_Status_Provider;
/**
 * Removes stored token and triggers subscription process.
 */
class Re_Sign_Up implements Response_Handler_Interface
{
    public function __construct(private readonly Analytics_Token $analytics_token, private readonly Subscription_Handler $subscription_handler, private readonly Subscription_Status_Provider $subscription_status_provider)
    {
    }
    /**
     * @inheritdoc
     */
    public function handle_response(array $response_body): bool
    {
        if ($this->subscription_status_provider->get_status() === Subscription_Status_Provider::ENABLED) {
            $this->analytics_token->store_token(null);
            $this->subscription_handler->process_enabled();
        }
        return false;
    }
}