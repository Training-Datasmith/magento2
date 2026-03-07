<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Config\Backend\Baseurl;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class for processing of change of Base URL.
 */
class SubscriptionUpdateHandler
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
    private int $attemptsInitValue = 48;

    /**
     * Cron expression for a update handler.
     */
    private string $cronExpression = '0 * * * *';

    public function __construct(private readonly AnalyticsToken $analyticsToken, private readonly FlagManager $flagManager, private readonly ReinitableConfigInterface $reinitableConfig, private readonly WriterInterface $configWriter)
    {
    }

    /**
     * Activate process of subscription update handling.
     */
    public function processUrlUpdate(string $url): bool
    {
        if ($this->analyticsToken->isTokenExist()) {
            if (!$this->flagManager->getFlagData(self::PREVIOUS_BASE_URL_FLAG_CODE)) {
                $this->flagManager->saveFlag(self::PREVIOUS_BASE_URL_FLAG_CODE, $url);
            }

            $this->flagManager
                ->saveFlag(self::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue);
            $this->configWriter->save(self::UPDATE_CRON_STRING_PATH, $this->cronExpression);
            $this->reinitableConfig->reinit();
        }

        return true;
    }
}
