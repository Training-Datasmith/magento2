<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Active subscription process for Advanced Reporting
 */
class PrepareInitialConfig implements DataPatchInterface, PatchVersionInterface
{
    private string $subscriptionEnabledConfigPath = 'analytics/subscription/enabled';

    public function __construct(private readonly ModuleDataSetupInterface $moduleDataSetup, private readonly SubscriptionHandler $subscriptionHandler)
    {
    }

    /**
     * @inheritDoc
     */
    public function apply(): static
    {
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => $this->subscriptionEnabledConfigPath,
                'value' => Enabledisable::ENABLE_VALUE,
            ]
        );

        $this->subscriptionHandler->processEnabled();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '2.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
