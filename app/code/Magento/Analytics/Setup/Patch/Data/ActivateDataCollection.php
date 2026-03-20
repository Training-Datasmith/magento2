<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\Collection_Time;
use Magento\Analytics\Model\Subscription_Status_Provider;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
/**
 * Activate data collection mechanism
 */
class Activate_Data_Collection implements Data_Patch_Interface
{
    private string $analytics_collection_time_config_path = 'analytics/general/collection_time';
    public function __construct(private readonly Scope_Config_Interface $scope_config, private readonly Subscription_Status_Provider $subscription_status_provider, private readonly Collection_Time $collection_time_backend_model)
    {
    }
    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function apply(): static
    {
        $subscription_status = $this->subscription_status_provider->get_status();
        $is_collection_process_activated = $this->scope_config->get_value(Collection_Time::CRON_SCHEDULE_PATH);
        if ($subscription_status !== $this->subscription_status_provider->get_status_for_disabled_subscription() && !$is_collection_process_activated) {
            $this->collection_time_backend_model->set_value($this->scope_config->get_value($this->analytics_collection_time_config_path));
            $this->collection_time_backend_model->set_path($this->analytics_collection_time_config_path);
            $this->collection_time_backend_model->after_save();
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function get_aliases(): array
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public static function get_dependencies(): array
    {
        return [Prepare_Initial_Config::class];
    }
}