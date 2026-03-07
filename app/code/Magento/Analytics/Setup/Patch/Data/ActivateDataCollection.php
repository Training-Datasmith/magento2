<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Activate data collection mechanism
 */
class ActivateDataCollection implements DataPatchInterface
{
    private string $analyticsCollectionTimeConfigPath = 'analytics/general/collection_time';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig, private readonly SubscriptionStatusProvider $subscriptionStatusProvider, private readonly CollectionTime $collectionTimeBackendModel)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function apply(): static
    {
        $subscriptionStatus = $this->subscriptionStatusProvider->getStatus();
        $isCollectionProcessActivated = $this->scopeConfig->getValue(CollectionTime::CRON_SCHEDULE_PATH);
        if ($subscriptionStatus !== $this->subscriptionStatusProvider->getStatusForDisabledSubscription()
            && !$isCollectionProcessActivated
        ) {
            $this->collectionTimeBackendModel
                ->setValue($this->scopeConfig->getValue($this->analyticsCollectionTimeConfigPath));
            $this->collectionTimeBackendModel->setPath($this->analyticsCollectionTimeConfigPath);
            $this->collectionTimeBackendModel->afterSave();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            PrepareInitialConfig::class,
        ];
    }
}
