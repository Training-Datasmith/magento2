<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue\Config\Reader;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\Config\Reader\Env\Converter\Publisher as PublisherConverter;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class Env implements \Magento\Framework\Config\ReaderInterface
{
    public const ENV_QUEUE  = 'queue';
    public const ENV_PUBLISHERS  = 'publishers';
    public const ENV_TOPICS = 'topics';
    public const ENV_CONSUMERS = 'consumers';
    public const ENV_CONSUMER_CONNECTION = 'connection';
    public const ENV_CONSUMER_MAX_MESSAGES = 'max_messages';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var PublisherConverter
     */
    private $publisherConverter;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param PublisherConverter|null $publisherConverter
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ?PublisherConverter $publisherConverter = null
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->publisherConverter = $publisherConverter ?: ObjectManager::getInstance()->get(PublisherConverter::class);
    }

    /**
     * Read communication configuration from env.php
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_QUEUE) ?: [];
        if (isset($configData['config'])) {
            $convertedConfigData = $this->publisherConverter->convert($configData['config']);
            unset($configData['config']);
            $configData = array_replace_recursive($configData, $convertedConfigData);
        }

        return $configData;
    }
}
