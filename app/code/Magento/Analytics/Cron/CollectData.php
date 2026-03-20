<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Export_Data_Handler_Interface;
use Magento\Analytics\Model\Subscription_Status_Provider;
/**
 * Cron for data collection by a schedule for MBI.
 */
class Collect_Data
{
    public function __construct(
        /**
         * Resource for the handling of a new data collection.
         */
        private readonly Export_Data_Handler_Interface $export_data_handler,
        /**
         * Resource which provides a status of subscription.
         */
        private readonly Subscription_Status_Provider $subscription_status
    )
    {
    }
    /**
     * Run data export preparation
     */
    public function execute(): bool
    {
        if ($this->subscription_status->get_status() === Subscription_Status_Provider::ENABLED) {
            $this->export_data_handler->prepare_export_data();
        }
        return true;
    }
}