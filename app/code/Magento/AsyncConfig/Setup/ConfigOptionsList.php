<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOptionFactory;

/**
 * Deployment configuration options required for the Config module.
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the option
     */
    public const INPUT_KEY_ASYNC_CONFIG_SAVE = 'config-async';

    /**
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_ASYNC_CONFIG_SAVE = 'config/async';

    /**
     * Default value
     */
    private const DEFAULT_ASYNC_CONFIG = 0;

    /**
     * The available configuration values
     */
    private array $selectOptions = [0, 1];

    /**
     * @var SelectConfigOptionFactory
     */
    private $selectConfigOptionFactory;

    public function __construct(
        private readonly ConfigDataFactory $configDataFactory,
        SelectConfigOptionFactory $selectConfigOptionFactory
    ) {
        $this->selectConfigOptionFactory = $selectConfigOptionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return [
            $this->selectConfigOptionFactory->create(
                [
                    'name' => self::INPUT_KEY_ASYNC_CONFIG_SAVE,
                    'frontendType' => SelectConfigOption::FRONTEND_WIZARD_SELECT,
                    'selectOptions' => $this->selectOptions,
                    'configPath' => self::CONFIG_PATH_ASYNC_CONFIG_SAVE,
                    'description' => 'Enable async Admin Config Save? 1 - Yes, 0 - No',
                    'defaultValue' => self::DEFAULT_ASYNC_CONFIG,
                ]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig): array
    {
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

        if (!$this->isDataEmpty($data, self::INPUT_KEY_ASYNC_CONFIG_SAVE)) {
            $configData->set(
                self::CONFIG_PATH_ASYNC_CONFIG_SAVE,
                (int)$data[self::INPUT_KEY_ASYNC_CONFIG_SAVE]
            );
        }

        return [$configData];
    }

    /**
     * @inheritdoc
     * @return list<'You can use only 1 or 0 for config-async option'>
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig): array
    {
        $errors = [];

        if (!$this->isDataEmpty($options, self::INPUT_KEY_ASYNC_CONFIG_SAVE) &&
            !in_array(
                $options[self::INPUT_KEY_ASYNC_CONFIG_SAVE],
                $this->selectOptions
            )
        ) {
            $errors[] = 'You can use only 1 or 0 for ' . self::INPUT_KEY_ASYNC_CONFIG_SAVE . ' option';
        }

        return $errors;
    }

    /**
     * Check if data ($data) with key ($key) is empty
     */
    private function isDataEmpty(array $data, string $key): bool
    {
        if (isset($data[$key]) && $data[$key] !== '') {
            return false;
        }
        return true;
    }
}
