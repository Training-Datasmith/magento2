<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\FlagManager;

/**
 * Executes by cron schedule in case base url was changed
 */
class Update
{
    public function __construct(private readonly Connector $connector, private readonly WriterInterface $configWriter, private readonly ReinitableConfigInterface $reinitableConfig, private readonly FlagManager $flagManager, private readonly AnalyticsToken $analyticsToken)
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
        $attemptsCount = (int)$this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);

        if (($attemptsCount > 0) && $this->analyticsToken->isTokenExist()) {
            $attemptsCount--;
            $this->flagManager
                ->saveFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
            $result = $this->connector->execute('update');
        }

        if ($result || ($attemptsCount <= 0) || (!$this->analyticsToken->isTokenExist())) {
            $this->exitFromUpdateProcess();
        }

        return $result;
    }

    /**
     * Clean-up flags and refresh configuration
     */
    private function exitFromUpdateProcess(): void
    {
        $this->flagManager
            ->deleteFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
        $this->flagManager->deleteFlag(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE);
        $this->configWriter->delete(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH);
        $this->reinitableConfig->reinit();
    }
}
