<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\Subscription_Update_Handler;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Config\Value;
use Magento\Store\Model\Store;
/**
 * Plugin on Base URL config value AfterSave method.
 */
class Base_Url_Config_Plugin
{
    public function __construct(private readonly Subscription_Update_Handler $subscription_update_handler)
    {
    }
    /**
     * Add additional handling after config value was saved.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_after_save(Value $subject, Value $result): Value
    {
        if ($this->is_plugin_applicable($result)) {
            $this->subscription_update_handler->process_url_update($result->get_old_value());
        }
        return $result;
    }
    /**
     * Check is need to apply the plugin logic
     */
    private function is_plugin_applicable(Value $result): bool
    {
        return $result->is_value_changed() && $result->get_path() === Store::XML_PATH_SECURE_BASE_URL && $result->get_scope() === Scope_Config_Interface::SCOPE_TYPE_DEFAULT;
    }
}