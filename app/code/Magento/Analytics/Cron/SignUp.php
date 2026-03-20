<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Config\Backend\Enabled\Subscription_Handler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\Reinitable_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Flag_Manager;
/**
 * Cron class for the Advanced Reporting signup process
 */
class Sign_Up
{
    public function __construct(
        private readonly Connector $connector,
        private readonly Writer_Interface $config_writer,
        private readonly Flag_Manager $flag_manager,
        /**
         * Reinitable Config Model.
         */
        private readonly Reinitable_Config_Interface $reinitable_config
    )
    {
    }
    /**
     * Execute scheduled subscription operation.
     *
     * In case of failure writes message to notifications inbox
     *
     * @throws NotFoundException
     */
    public function execute(): bool
    {
        $attempts_count = (int) $this->flag_manager->get_flag_data(Subscription_Handler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        if ($attempts_count <= 0) {
            $this->delete_analytics_cron_expr();
            $this->flag_manager->delete_flag(Subscription_Handler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
            return false;
        }
        $attempts_count--;
        $this->flag_manager->save_flag(Subscription_Handler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attempts_count);
        $sign_up_result = $this->connector->execute('signUp');
        if ($sign_up_result === false) {
            return false;
        }
        $this->delete_analytics_cron_expr();
        $this->flag_manager->delete_flag(Subscription_Handler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        return true;
    }
    /**
     * Delete cron schedule setting into config.
     *
     * Delete cron schedule setting for subscription handler into config and
     * re-initialize config cache to avoid auto-generate new schedule items.
     */
    private function delete_analytics_cron_expr(): bool
    {
        $this->config_writer->delete(Subscription_Handler::CRON_STRING_PATH);
        $this->reinitable_config->reinit();
        return true;
    }
}