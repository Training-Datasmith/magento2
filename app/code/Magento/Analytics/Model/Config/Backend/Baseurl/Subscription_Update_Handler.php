<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config\Backend\Baseurl;

use Magento\Analytics\Model\Analytics_Token;
use Magento\Framework\App\Config\Reinitable_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
use Magento\Framework\Flag_Manager;
/**
 * Class for processing of change of Base URL.
 */
class Subscription_Update_Handler
{
    /**
     * Flag code for a reserve counter to update subscription.
     */
    public const SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE = 'analytics_link_subscription_update_reverse_counter';
    /**
     * Config path for schedule setting of update handler.
     */
    public const UPDATE_CRON_STRING_PATH = 'crontab/analytics/jobs/analytics_update/schedule/cron_expr';
    /**
     * Flag code for the previous Base URL.
     */
    public const PREVIOUS_BASE_URL_FLAG_CODE = 'analytics_previous_base_url';
    /**
     * Max value for a reserve counter to update subscription.
     */
    private int $attempts_init_value = 48;
    /**
     * Cron expression for a update handler.
     */
    private string $cron_expression = '0 * * * *';
    public function __construct(private readonly Analytics_Token $analytics_token, private readonly Flag_Manager $flag_manager, private readonly Reinitable_Config_Interface $reinitable_config, private readonly Writer_Interface $config_writer)
    {
    }
    /**
     * Activate process of subscription update handling.
     */
    public function process_url_update(string $url): bool
    {
        if ($this->analytics_token->is_token_exist()) {
            if (!$this->flag_manager->get_flag_data(self::PREVIOUS_BASE_URL_FLAG_CODE)) {
                $this->flag_manager->save_flag(self::PREVIOUS_BASE_URL_FLAG_CODE, $url);
            }
            $this->flag_manager->save_flag(self::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $this->attempts_init_value);
            $this->config_writer->save(self::UPDATE_CRON_STRING_PATH, $this->cron_expression);
            $this->reinitable_config->reinit();
        }
        return true;
    }
}