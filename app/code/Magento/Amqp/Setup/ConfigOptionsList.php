<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Amqp\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
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

    public function __construct(private readonly ConnectionValidator $connectionValidator)
    {
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_HOST,
                'Amqp server host',
                self::DEFAULT_AMQP_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_PORT,
                'Amqp server port',
                self::DEFAULT_AMQP_PORT
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_USER,
                'Amqp server username',
                self::DEFAULT_AMQP_USER
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_PASSWORD,
                'Amqp server password',
                self::DEFAULT_AMQP_PASSWORD
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST,
                'Amqp virtualhost',
                self::DEFAULT_AMQP_VIRTUAL_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_SSL,
                'Amqp SSL',
                self::DEFAULT_AMQP_SSL
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS,
                TextConfigOption::FRONTEND_WIZARD_TEXTAREA,
                self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS,
                'Amqp SSL Options (JSON)',
                self::DEFAULT_AMQP_SSL
            ),
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig): array
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_HOST)) {
            $configData->set(self::CONFIG_PATH_QUEUE_AMQP_HOST, $data[self::INPUT_KEY_QUEUE_AMQP_HOST]);
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_PORT)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_PORT, $data[self::INPUT_KEY_QUEUE_AMQP_PORT]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_USER)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_USER, $data[self::INPUT_KEY_QUEUE_AMQP_USER]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_PASSWORD)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_PASSWORD, $data[self::INPUT_KEY_QUEUE_AMQP_PASSWORD]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST)) {
                $configData->set(
                    self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST,
                    $data[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST]
                );
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_SSL)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_SSL, $data[self::INPUT_KEY_QUEUE_AMQP_SSL]);
            }
            if (!$this->isDataEmpty(
                $data,
                self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS
            )) {
                $options = json_decode(
                    (string) $data[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS],
                    true
                );
                if ($options !== null) {
                    $configData->set(
                        self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS,
                        $options
                    );
                }
            }
        }

        return [$configData];
    }

    /**
     * @inheritdoc
     * @return list<'Could not connect to the Amqp Server.'>
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig): array
    {
        $errors = [];

        if (isset($options[self::INPUT_KEY_QUEUE_AMQP_HOST])
            && $options[self::INPUT_KEY_QUEUE_AMQP_HOST] !== '') {
            if (!$this->isDataEmpty(
                $options,
                self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS
            )) {
                $sslOptions = json_decode(
                    (string) $options[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS],
                    true
                );
            } else {
                $sslOptions = null;
            }
            $isSslEnabled = !empty($options[self::INPUT_KEY_QUEUE_AMQP_SSL])
                && $options[self::INPUT_KEY_QUEUE_AMQP_SSL] !== 'false';

            $result = $this->connectionValidator->isConnectionValid(
                $options[self::INPUT_KEY_QUEUE_AMQP_HOST],
                $options[self::INPUT_KEY_QUEUE_AMQP_PORT],
                $options[self::INPUT_KEY_QUEUE_AMQP_USER],
                $options[self::INPUT_KEY_QUEUE_AMQP_PASSWORD],
                $options[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST],
                $isSslEnabled,
                $sslOptions
            );

            if (!$result) {
                $errors[] = 'Could not connect to the Amqp Server.';
            }

            if (isset($options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION])
                && $options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION] !== 'amqp') {
                $errors = [];
            }
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
