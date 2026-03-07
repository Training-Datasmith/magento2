<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\OTPRequest;
use Magento\Analytics\Model\Exception\State\SubscriptionUpdateException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;

/**
 * Provide URL on resource with reports.
 */
class ReportUrlProvider
{
    /**
     * Path to config value with URL which provide reports.
     */
    private string $urlReportConfigPath = 'analytics/url/report';

    /**
     * Path to Advanced Reporting documentation URL.
     */
    private string $urlReportDocConfigPath = 'analytics/url/documentation';

    public function __construct(
        /**
         * Resource for handling MBI token value.
         */
        private readonly AnalyticsToken $analyticsToken,
        /**
         * Resource which provide OTP.
         */
        private readonly OTPRequest $otpRequest,
        private readonly ScopeConfigInterface $config,
        private readonly FlagManager $flagManager
    ) {
    }

    /**
     * Provide URL on resource with reports.
     *
     * @return string
     * @throws SubscriptionUpdateException
     */
    public function getUrl()
    {
        if ($this->flagManager->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)) {
            throw new SubscriptionUpdateException(__(
                'Your Base URL has been changed and your reports are being updated. '
                . 'Advanced Reporting will be available once this change has been processed. Please try again later.'
            ));
        }

        if ($this->analyticsToken->isTokenExist()) {
            $url = $this->config->getValue($this->urlReportConfigPath);
            $otp = $this->otpRequest->call();
            if ($otp) {
                $query = http_build_query(['otp' => $otp], '', '&');
                $url .= '?' . $query;
            }
        } else {
            $url = $this->config->getValue($this->urlReportDocConfigPath);
        }

        return $url;
    }
}
