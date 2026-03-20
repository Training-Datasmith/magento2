<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Read config from different sources and aggregate them
 *
 * @package Magento\Framework\Config
 */
class Reader implements \Magento\Framework\App\Config\Scope\Reader_Interface
{
    /**
     * @var array
     */
    private $sources;
    /**
     * @param array $sources
     */
    public function __construct(array $sources)
    {
        $this->sources = $this->prepare_sources($sources);
    }
    /**
     * Read configuration data
     *
     * @param null|string $scope
     * @throws LocalizedException Exception is thrown when scope other than default is given
     * @return array
     */
    public function read($scope = null)
    {
        $config = [];
        foreach ($this->sources as $source_data) {
            /** @var \Magento\Framework\App\Config\Reader\Source\SourceInterface $source */
            $source = $source_data['class'];
            $config = array_replace_recursive($config, $source->get($scope));
        }
        return $config;
    }
    /**
     * Prepare source for usage
     *
     * @param array $array
     * @return array
     */
    private function prepare_sources(array $array)
    {
        $array = array_filter($array, function ($item) {
            return (!isset($item['disable']) || !$item['disable']) && $item['class'];
        });
        uasort($array, function ($first_item, $nex_item) {
            return (int) $first_item['sortOrder'] <=> (int) $nex_item['sortOrder'];
        });
        return $array;
    }
}