<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\Subscription_Update_Handler;
use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Flag_Manager;
/**
 * Provider of subscription status.
 */
class Subscription_Status_Provider
{
    /**
     * Represents an enabled subscription state.
     */
    public const ENABLED = 'Enabled';
    /**
     * Represents a failed subscription state.
     */
    public const FAILED = 'Failed';
    /**
     * Represents a pending subscription state.
     */
    public const PENDING = 'Pending';
    /**
     * Represents a disabled subscription state.
     */
    public const DISABLED = 'Disabled';
    public function __construct(private readonly Scope_Config_Interface $scope_config, private readonly Analytics_Token $analytics_token, private readonly Flag_Manager $flag_manager)
    {
    }
    /**
     * Retrieve subscription status to Magento BI Advanced Reporting.
     *
     * Statuses:
     * Enabled - if subscription is enabled and MA token was received;
     * Pending - if subscription is enabled and MA token was not received;
     * Disabled - if subscription is not enabled.
     * Failed - if subscription is enabled and token was not received after attempts ended.
     *
     * @return string
     */
    public function get_status()
    {
        $is_subscription_enabled_in_config = $this->scope_config->get_value('analytics/subscription/enabled');
        if ($is_subscription_enabled_in_config) {
            return $this->get_status_for_enabled_subscription();
        }
        return $this->get_status_for_disabled_subscription();
    }
    /**
     * Retrieve status for subscription that enabled in config.
     *
     * @return string
     */
    public function get_status_for_enabled_subscription()
    {
        $status = static::ENABLED;
        if ($this->flag_manager->get_flag_data(Subscription_Update_Handler::PREVIOUS_BASE_URL_FLAG_CODE)) {
            $status = self::PENDING;
        }
        if (!$this->analytics_token->is_token_exist()) {
            $status = static::PENDING;
            if ($this->flag_manager->get_flag_data(Subscription_Handler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE) === null) {
                $status = static::FAILED;
            }
        }
        return $status;
    }
    /**
     * Retrieve status for subscription that disabled in config.
     *
     * @return string
     */
    public function get_status_for_disabled_subscription()
    {
        return static::DISABLED;
    }
}