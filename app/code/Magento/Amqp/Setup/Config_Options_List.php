<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Config\Data\Config_Data;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Setup\Config_Options_List_Interface;
use Magento\Framework\Setup\Option\Text_Config_Option;
/**
 * Deployment configuration options needed for Setup application
 */
class Config_Options_List implements Config_Options_List_Interface
{
    /**
     * Input key for the options
     */
    public const INPUT_KEY_QUEUE_AMQP_HOST = 'amqp-host';
    public const INPUT_KEY_QUEUE_AMQP_PORT = 'amqp-port';
    public const INPUT_KEY_QUEUE_AMQP_USER = 'amqp-user';
    public const INPUT_KEY_QUEUE_AMQP_PASSWORD = 'amqp-password';
    public const INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST = 'amqp-virtualhost';
    public const INPUT_KEY_QUEUE_AMQP_SSL = 'amqp-ssl';
    public const INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS = 'amqp-ssl-options';
    public const INPUT_KEY_QUEUE_DEFAULT_CONNECTION = 'queue-default-connection';
    /**
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_QUEUE_AMQP_HOST = 'queue/amqp/host';
    public const CONFIG_PATH_QUEUE_AMQP_PORT = 'queue/amqp/port';
    public const CONFIG_PATH_QUEUE_AMQP_USER = 'queue/amqp/user';
    public const CONFIG_PATH_QUEUE_AMQP_PASSWORD = 'queue/amqp/password';
    public const CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST = 'queue/amqp/virtualhost';
    public const CONFIG_PATH_QUEUE_AMQP_SSL = 'queue/amqp/ssl';
    public const CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS = 'queue/amqp/ssl_options';
    /**
     * Default values
     */
    public const DEFAULT_AMQP_HOST = '';
    public const DEFAULT_AMQP_PORT = '5672';
    public const DEFAULT_AMQP_USER = '';
    public const DEFAULT_AMQP_PASSWORD = '';
    public const DEFAULT_AMQP_VIRTUAL_HOST = '/';
    public const DEFAULT_AMQP_SSL = '';
    public function __construct(private readonly Connection_Validator $connection_validator)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_options(): array
    {
        return [new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_HOST, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_HOST, 'Amqp server host', self::DEFAULT_AMQP_HOST), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_PORT, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_PORT, 'Amqp server port', self::DEFAULT_AMQP_PORT), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_USER, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_USER, 'Amqp server username', self::DEFAULT_AMQP_USER), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_PASSWORD, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_PASSWORD, 'Amqp server password', self::DEFAULT_AMQP_PASSWORD), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST, 'Amqp virtualhost', self::DEFAULT_AMQP_VIRTUAL_HOST), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_SSL, Text_Config_Option::FRONTEND_WIZARD_TEXT, self::CONFIG_PATH_QUEUE_AMQP_SSL, 'Amqp SSL', self::DEFAULT_AMQP_SSL), new Text_Config_Option(self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS, Text_Config_Option::FRONTEND_WIZARD_TEXTAREA, self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS, 'Amqp SSL Options (JSON)', self::DEFAULT_AMQP_SSL)];
    }
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create_config(array $data, Deployment_Config $deployment_config): array
    {
        $config_data = new Config_Data(Config_File_Pool::APP_ENV);
        if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_HOST)) {
            $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_HOST, $data[self::INPUT_KEY_QUEUE_AMQP_HOST]);
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_PORT)) {
                $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_PORT, $data[self::INPUT_KEY_QUEUE_AMQP_PORT]);
            }
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_USER)) {
                $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_USER, $data[self::INPUT_KEY_QUEUE_AMQP_USER]);
            }
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_PASSWORD)) {
                $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_PASSWORD, $data[self::INPUT_KEY_QUEUE_AMQP_PASSWORD]);
            }
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST)) {
                $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST, $data[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST]);
            }
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_SSL)) {
                $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_SSL, $data[self::INPUT_KEY_QUEUE_AMQP_SSL]);
            }
            if (!$this->is_data_empty($data, self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS)) {
                $options = json_decode((string) $data[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS], true);
                if ($options !== null) {
                    $config_data->set(self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS, $options);
                }
            }
        }
        return [$config_data];
    }
    /**
     * @inheritdoc
     * @return list<'Could not connect to the Amqp Server.'>
     */
    public function validate(array $options, Deployment_Config $deployment_config): array
    {
        $errors = [];
        if (isset($options[self::INPUT_KEY_QUEUE_AMQP_HOST]) && $options[self::INPUT_KEY_QUEUE_AMQP_HOST] !== '') {
            if (!$this->is_data_empty($options, self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS)) {
                $ssl_options = json_decode((string) $options[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS], true);
            } else {
                $ssl_options = null;
            }
            $is_ssl_enabled = !empty($options[self::INPUT_KEY_QUEUE_AMQP_SSL]) && $options[self::INPUT_KEY_QUEUE_AMQP_SSL] !== 'false';
            $result = $this->connection_validator->is_connection_valid($options[self::INPUT_KEY_QUEUE_AMQP_HOST], $options[self::INPUT_KEY_QUEUE_AMQP_PORT], $options[self::INPUT_KEY_QUEUE_AMQP_USER], $options[self::INPUT_KEY_QUEUE_AMQP_PASSWORD], $options[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST], $is_ssl_enabled, $ssl_options);
            if (!$result) {
                $errors[] = 'Could not connect to the Amqp Server.';
            }
            if (isset($options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION]) && $options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION] !== 'amqp') {
                $errors = [];
            }
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