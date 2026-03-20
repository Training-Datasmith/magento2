<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Config\Backend\Baseurl\Subscription_Update_Handler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\Reinitable_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Flag_Manager;
/**
 * Executes by cron schedule in case base url was changed
 */
class Update
{
    public function __construct(private readonly Connector $connector, private readonly Writer_Interface $config_writer, private readonly Reinitable_Config_Interface $reinitable_config, private readonly Flag_Manager $flag_manager, private readonly Analytics_Token $analytics_token)
    {
    }
    /**
     * Execute scheduled update operation
     *
     * @return bool
     * @throws NotFoundException
     */
    public function execute()
    {
        $result = false;
        $attempts_count = (int) $this->flag_manager->get_flag_data(Subscription_Update_Handler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
        if ($attempts_count > 0 && $this->analytics_token->is_token_exist()) {
            $attempts_count--;
            $this->flag_manager->save_flag(Subscription_Update_Handler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $attempts_count);
            $result = $this->connector->execute('update');
        }
        if ($result || $attempts_count <= 0 || !$this->analytics_token->is_token_exist()) {
            $this->exit_from_update_process();
        }
        return $result;
    }
    /**
     * Clean-up flags and refresh configuration
     */
    private function exit_from_update_process(): void
    {
        $this->flag_manager->delete_flag(Subscription_Update_Handler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
        $this->flag_manager->delete_flag(Subscription_Update_Handler::PREVIOUS_BASE_URL_FLAG_CODE);
        $this->config_writer->delete(Subscription_Update_Handler::UPDATE_CRON_STRING_PATH);
        $this->reinitable_config->reinit();
    }
}