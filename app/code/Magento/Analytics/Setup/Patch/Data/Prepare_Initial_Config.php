<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Setup\Module_Data_Setup_Interface;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
use Magento\Framework\Setup\Patch\Patch_Version_Interface;
/**
 * Active subscription process for Advanced Reporting
 */
class Prepare_Initial_Config implements Data_Patch_Interface, Patch_Version_Interface
{
    private string $subscription_enabled_config_path = 'analytics/subscription/enabled';
    public function __construct(private readonly Module_Data_Setup_Interface $module_data_setup, private readonly Subscription_Handler $subscription_handler)
    {
    }
    /**
     * @inheritDoc
     */
    public function apply(): static
    {
        $this->module_data_setup->get_connection()->insert($this->module_data_setup->get_table('core_config_data'), ['path' => $this->subscription_enabled_config_path, 'value' => Enabledisable::ENABLE_VALUE]);
        $this->subscription_handler->process_enabled();
        return $this;
    }
    /**
     * @inheritDoc
     */
    public static function get_dependencies(): array
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public static function get_version(): string
    {
        return '2.0.0';
    }
    /**
     * @inheritDoc
     */
    public function get_aliases(): array
    {
        return [];
    }
}