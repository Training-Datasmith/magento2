<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\Subscription_Update_Handler;
use Magento\Analytics\Model\Connector\Otp_Request;
use Magento\Analytics\Model\Exception\State\Subscription_Update_Exception;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Flag_Manager;
/**
 * Provide URL on resource with reports.
 */
class Report_Url_Provider
{
    /**
     * Path to config value with URL which provide reports.
     */
    private string $url_report_config_path = 'analytics/url/report';
    /**
     * Path to Advanced Reporting documentation URL.
     */
    private string $url_report_doc_config_path = 'analytics/url/documentation';
    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly Analytics_Token $analytics_token,
        /**
         * Resource which provide OTP.
         */
        private readonly Otp_Request $otp_request,
        private readonly Scope_Config_Interface $config,
        private readonly Flag_Manager $flag_manager
    )
    {
    }
    /**
     * Provide URL on resource with reports.
     *
     * @return string
     * @throws SubscriptionUpdateException
     */
    public function get_url()
    {
        if ($this->flag_manager->get_flag_data(Subscription_Update_Handler::PREVIOUS_BASE_URL_FLAG_CODE)) {
            throw new Subscription_Update_Exception(__('Your Base URL has been changed and your reports are being updated. ' . 'Advanced Reporting will be available once this change has been processed. Please try again later.'));
        }
        if ($this->analytics_token->is_token_exist()) {
            $url = $this->config->get_value($this->url_report_config_path);
            $otp = $this->otp_request->call();
            if ($otp) {
                $query = http_build_query(['otp' => $otp], '', '&');
                $url .= '?' . $query;
            }
        } else {
            $url = $this->config->get_value($this->url_report_doc_config_path);
        }
        return $url;
    }
}