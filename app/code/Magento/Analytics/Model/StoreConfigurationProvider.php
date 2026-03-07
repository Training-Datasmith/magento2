<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides config data report
 */
class StoreConfigurationProvider
{
    /**
     * @param string[] $configPaths
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig, private readonly StoreManagerInterface $storeManager, private readonly array $configPaths)
    {
    }

    /**
     * Generates report using config paths from di.xml
     *
     * For each website and store
     */
    public function getReport(): \IteratorIterator
    {
        $configReport = $this->generateReportForScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        /** @var WebsiteInterface $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $configReport = array_merge(
                $this->generateReportForScope(ScopeInterface::SCOPE_WEBSITES, $website->getId()),
                $configReport
            );
        }

        /** @var StoreInterface $store */
        foreach ($this->storeManager->getStores() as $store) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $configReport = array_merge(
                $this->generateReportForScope(ScopeInterface::SCOPE_STORES, $store->getId()),
                $configReport
            );
        }
        return new \IteratorIterator(new \ArrayIterator($configReport));
    }

    /**
     * Creates report from config for scope type and scope id.
     *
     * @param int $scopeId
     */
    private function generateReportForScope(string $scope, $scopeId): array
    {
        $report = [];
        foreach ($this->configPaths as $configPath) {
            $report[] = [
                'config_path' => $configPath,
                'scope' => $scope,
                'scope_id' => $scopeId,
                'value' => $this->scopeConfig->getValue(
                    $configPath,
                    $scope,
                    $scopeId
                ),
            ];
        }
        return $report;
    }
}
