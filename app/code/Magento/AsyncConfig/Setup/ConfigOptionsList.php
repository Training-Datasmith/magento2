<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Setup;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Config\Data\Config_Data_Factory;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Setup\Config_Options_List_Interface;
use Magento\Framework\Setup\Option\Select_Config_Option;
use Magento\Framework\Setup\Option\Select_Config_Option_Factory;
/**
 * Deployment configuration options required for the Config module.
 */
class Config_Options_List implements Config_Options_List_Interface
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
    private array $select_options = [0, 1];
    /**
     * @var SelectConfigOptionFactory
     */
    private $select_config_option_factory;
    public function __construct(private readonly Config_Data_Factory $config_data_factory, Select_Config_Option_Factory $select_config_option_factory)
    {
        $this->select_config_option_factory = $select_config_option_factory;
    }
    /**
     * @inheritdoc
     */
    public function get_options(): array
    {
        return [$this->select_config_option_factory->create(['name' => self::INPUT_KEY_ASYNC_CONFIG_SAVE, 'frontendType' => Select_Config_Option::FRONTEND_WIZARD_SELECT, 'selectOptions' => $this->select_options, 'configPath' => self::CONFIG_PATH_ASYNC_CONFIG_SAVE, 'description' => 'Enable async Admin Config Save? 1 - Yes, 0 - No', 'defaultValue' => self::DEFAULT_ASYNC_CONFIG])];
    }
    /**
     * @inheritdoc
     */
    public function create_config(array $data, Deployment_Config $deployment_config): array
    {
        $config_data = $this->config_data_factory->create(Config_File_Pool::APP_ENV);
        if (!$this->is_data_empty($data, self::INPUT_KEY_ASYNC_CONFIG_SAVE)) {
            $config_data->set(self::CONFIG_PATH_ASYNC_CONFIG_SAVE, (int) $data[self::INPUT_KEY_ASYNC_CONFIG_SAVE]);
        }
        return [$config_data];
    }
    /**
     * @inheritdoc
     * @return list<'You can use only 1 or 0 for config-async option'>
     */
    public function validate(array $options, Deployment_Config $deployment_config): array
    {
        $errors = [];
        if (!$this->is_data_empty($options, self::INPUT_KEY_ASYNC_CONFIG_SAVE) && !in_array($options[self::INPUT_KEY_ASYNC_CONFIG_SAVE], $this->select_options)) {
            $errors[] = 'You can use only 1 or 0 for ' . self::INPUT_KEY_ASYNC_CONFIG_SAVE . ' option';
        }
        return $errors;
    }
    /**
     * Check if data ($data) with key ($key) is empty
     */
    private function is_data_empty(array $data, string $key): bool
    {
        if (isset($data[$key]) && $data[$key] !== '') {
            return false;
        }
        return true;
    }
}