<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Communication\Config\Reader\Env_Reader\Validator;
/**
 * Communication configuration reader. Reads data from env.php.
 */
class Env_Reader implements \Magento\Framework\Config\Reader_Interface
{
    public const ENV_COMMUNICATION = 'communication';
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var Validator
     */
    private $env_validator;
    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Validator $envValidator
     */
    public function __construct(Deployment_Config $deployment_config, Validator $env_validator)
    {
        $this->deployment_config = $deployment_config;
        $this->env_validator = $env_validator;
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
        $config_data = $this->deployment_config->get_config_data(self::ENV_COMMUNICATION);
        if ($config_data) {
            $this->env_validator->validate($config_data);
        }
        return $config_data ?: [];
    }
}