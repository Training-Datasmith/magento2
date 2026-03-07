<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class for processing of activation/deactivation MBI subscription.
 */
class SubscriptionHandler
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
        '0',                    # Minute
        '*',                    # Hour
        '*',                    # Day of the Month
        '*',                    # Month of the Year
        '*',                    # Day of the Week
    ];

    /**
     * Max value for reserve counter of attempts to subscribe.
     */
    private int $attemptsInitValue = 24;

    public function __construct(
        /**
         * Service which allows to write values into config.
         */
        private readonly WriterInterface $configWriter,
        private readonly FlagManager $flagManager,
        /**
         * Model for handling Magento BI token value.
         */
        private readonly AnalyticsToken $analyticsToken,
        private readonly ReinitableConfigInterface $reinitableConfig
    ) {
    }

    /**
     * Processing of activation MBI subscription.
     *
     * Activate process of subscription handling if Analytics token is not received.
     */
    public function processEnabled(): bool
    {
        if (!$this->analyticsToken->isTokenExist()) {
            $this->setCronSchedule();
            $this->setAttemptsFlag();
            $this->reinitableConfig->reinit();
        }

        return true;
    }

    /**
     * Set cron schedule setting into config for activation of subscription process.
     */
    private function setCronSchedule(): bool
    {
        $this->configWriter->save(self::CRON_STRING_PATH, join(' ', self::CRON_EXPR_ARRAY));
        return true;
    }

    /**
     * Set flag as reserve counter of attempts subscription operation.
     *
     * @return bool
     */
    private function setAttemptsFlag()
    {
        return $this->flagManager
            ->saveFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue);
    }

    /**
     * Processing of deactivation MBI subscription.
     *
     * Disable data collection
     * and interrupt subscription handling if Analytics token is not received.
     */
    public function processDisabled(): bool
    {
        $this->disableCollectionData();

        if (!$this->analyticsToken->isTokenExist()) {
            $this->unsetAttemptsFlag();
        }

        return true;
    }

    /**
     * Unset flag of attempts subscription operation.
     *
     * @return bool
     */
    private function unsetAttemptsFlag()
    {
        return $this->flagManager
            ->deleteFlag(self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
    }

    /**
     * Unset schedule of collection data cron.
     */
    private function disableCollectionData(): bool
    {
        $this->configWriter->delete(CollectionTime::CRON_SCHEDULE_PATH);

        return true;
    }
}
