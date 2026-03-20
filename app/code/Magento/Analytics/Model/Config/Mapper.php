<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config;

/**
 * Transforms Analytics configuration data.
 */
class Mapper
{
    /**
     * Transforms Analytics configuration data.
     *
     * @return array $files
     * $files = [
     *    'file_name' => [
     *        'name' => 'file_name',
     *        'providers' => [
     *            'reportProvider' => [
     *                'name' => 'report_provider_name',
     *                'class' => 'Magento\Analytics\ReportXml\ReportProvider',
     *                'parameters' =>[
     *                    'name' => 'report_name',
     *                ],
     *            ],
     *            'customProvider' => [
     *                'name' => 'custom_provider_name',
     *                'class' => 'Magento\Analytics\Model\CustomProvider',
     *            ],
     *        ],
     *    ]
     * ];
     */
    public function execute(array $config_data): array
    {
        if (!isset($config_data['config'][0]['file'])) {
            return [];
        }
        $files = [];
        foreach ($config_data['config'][0]['file'] as $file_data) {
            /** just one set of providers is allowed by xsd */
            $providers = reset($file_data['providers']);
            foreach ($providers as $provider_type => $provider_data_set) {
                /** just one set of provider data is allowed by xsd */
                $provider_data = reset($provider_data_set);
                /** just one set of parameters is allowed by xsd */
                $provider_data['parameters'] = !empty($provider_data['parameters']) ? reset($provider_data['parameters']) : [];
                array_walk($provider_data['parameters'], function (&$array): void {
                    $array = reset($array);
                });
                $providers[$provider_type] = $provider_data;
            }
            $files[$file_data['name']] = $file_data;
            $files[$file_data['name']]['providers'] = $providers;
        }
        return $files;
    }
}