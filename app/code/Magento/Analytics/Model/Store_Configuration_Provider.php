<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Store\Api\Data\Store_Interface;
use Magento\Store\Api\Data\Website_Interface;
use Magento\Store\Model\Scope_Interface;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Provides config data report
 */
class Store_Configuration_Provider
{
    /**
     * @param string[] $configPaths
     */
    public function __construct(private readonly Scope_Config_Interface $scope_config, private readonly Store_Manager_Interface $store_manager, private readonly array $config_paths)
    {
    }
    /**
     * Generates report using config paths from di.xml
     *
     * For each website and store
     */
    public function get_report(): \Iterator_Iterator
    {
        $config_report = $this->generate_report_for_scope(Scope_Config_Interface::SCOPE_TYPE_DEFAULT, 0);
        /** @var WebsiteInterface $website */
        foreach ($this->store_manager->get_websites() as $website) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $config_report = array_merge($this->generate_report_for_scope(Scope_Interface::SCOPE_WEBSITES, $website->get_id()), $config_report);
        }
        /** @var StoreInterface $store */
        foreach ($this->store_manager->get_stores() as $store) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $config_report = array_merge($this->generate_report_for_scope(Scope_Interface::SCOPE_STORES, $store->get_id()), $config_report);
        }
        return new \Iterator_Iterator(new \ArrayIterator($config_report));
    }
    /**
     * Creates report from config for scope type and scope id.
     *
     * @param int $scopeId
     */
    private function generate_report_for_scope(string $scope, $scope_id): array
    {
        $report = [];
        foreach ($this->config_paths as $config_path) {
            $report[] = ['config_path' => $config_path, 'scope' => $scope, 'scope_id' => $scope_id, 'value' => $this->scope_config->get_value($config_path, $scope, $scope_id)];
        }
        return $report;
    }
}