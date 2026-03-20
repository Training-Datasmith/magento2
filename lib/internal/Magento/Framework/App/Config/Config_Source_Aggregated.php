<?php

declare (strict_types=1);
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

class Config_Source_Aggregated implements Config_Source_Interface
{
    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;
    /**
     * ConfigSourceAggregated constructor.
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
        uasort($this->sources, function ($first_item, $second_item) {
            return $first_item['sortOrder'] <=> $second_item['sortOrder'];
        });
    }
    /**
     * Retrieve aggregated configuration from all available sources.
     *
     * @param string $path
     * @return string|array
     */
    public function get($path = '')
    {
        $data = [];
        foreach ($this->sources as $source_config) {
            /** @var ConfigSourceInterface $source */
            $source = $source_config['source'];
            $config_data = $source->get($path);
            if (!is_array($config_data)) {
                $data = $config_data;
            } elseif (!empty($config_data)) {
                $data = array_replace_recursive(is_array($data) ? $data : [], $config_data);
            }
        }
        return $data;
    }
}