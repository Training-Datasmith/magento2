<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Config\Backend\Collection_Time;
use Magento\Framework\App\Config\Reinitable_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
use Magento\Framework\Flag_Manager;
/**
 * Class for processing of activation/deactivation MBI subscription.
 */
class Subscription_Handler
{
    /**
     * Flag code for reserve counter of attempts to subscribe.
     */
    public const ATTEMPTS_REVERSE_COUNTER_FLAG_CODE = 'analytics_link_attempts_reverse_counter';
    /**
     * Config path for schedule setting of subscription handler.
     */
    public const CRON_STRING_PATH = 'crontab/analytics/jobs/analytics_subscribe/schedule/cron_expr';
    /**
     * Config value for schedule setting of subscription handler.
     */
    public const CRON_EXPR_ARRAY = [
        '0',
        # Minute
        '*',
        # Hour
        '*',
        # Day of the Month
        '*',
        # Month of the Year
        '*',
    ];
    /**
     * Max value for reserve counter of attempts to subscribe.
     */
    private int $attempts_init_value = 24;
    public function __construct(
        /**
         * Service which allows to write values into config.
         */
        private readonly Writer_Interface $config_writer,
        private readonly Flag_Manager $flag_manager,
        /**
         * Model for handling Magento BI token value.
         */
        private readonly Analytics_Token $analytics_token,
        private readonly Reinitable_Config_Interface $reinitable_config
    )
    {
    }
    /**
     * Processing of activation MBI subscription.
     *
     * Activate process of subscription handling if Analytics token is not received.
     */
    public function process_enabled(): bool
    {
        if (!$this->analytics_token->is_token_exist()) {
            $this->set_cron_schedule();
            $this->set_attempts_flag();
            $this->reinitable_config->reinit();
        }
        return true;
    }
    /**
     * Set cron schedule setting into config for activation of subscription process.
     */
    private function set_cron_schedule(): bool
    {
        $this->config_writer->save(self::CRON_STRING_PATH, join(' ', self::CRON_EXPR_ARRAY));
        return true;
    }
    /**
     * Set flag as reserve counter of attempts subscription operation.
     *
     * @return bool
     */
    private function set_attempts_flag()
    {
        return $this->flag_manager->save_flag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attempts_init_value);
    }
    /**
     * Processing of deactivation MBI subscription.
     *
     * Disable data collection
     * and interrupt subscription handling if Analytics token is not received.
     */
    public function process_disabled(): bool
    {
        $this->disable_collection_data();
        if (!$this->analytics_token->is_token_exist()) {
            $this->unset_attempts_flag();
        }
        return true;
    }
    /**
     * Unset flag of attempts subscription operation.
     *
     * @return bool
     */
    private function unset_attempts_flag()
    {
        return $this->flag_manager->delete_flag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
    }
    /**
     * Unset schedule of collection data cron.
     */
    private function disable_collection_data(): bool
    {
        $this->config_writer->delete(Collection_Time::CRON_SCHEDULE_PATH);
        return true;
    }
}