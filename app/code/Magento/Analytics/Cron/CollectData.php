<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\ExportDataHandlerInterface;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Cron for data collection by a schedule for MBI.
 */
class CollectData
{
    public function __construct(
        /**
         * Resource for the handling of a new data collection.
         */
        private readonly ExportDataHandlerInterface $exportDataHandler,
        /**
         * Resource which provides a status of subscription.
         */
        private readonly SubscriptionStatusProvider $subscriptionStatus
    ) {
    }

    /**
     * Run data export preparation
     */
    public function execute(): bool
    {
        if ($this->subscriptionStatus->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->exportDataHandler->prepareExportData();
        }

        return true;
    }
}
