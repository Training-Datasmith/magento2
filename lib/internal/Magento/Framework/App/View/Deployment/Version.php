<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Deployment;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Config_Options_List_Constants;
use Psr\Log\Logger_Interface;
/**
 * Deployment version of static files
 */
class Version
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $app_state;
    /**
     * @var \Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $version_storage;
    /**
     * @var string
     */
    private $cached_value;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @param \Magento\Framework\App\State $appState
     * @param Version\StorageInterface $versionStorage
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(\Magento\Framework\App\State $app_state, \Magento\Framework\App\View\Deployment\Version\Storage_Interface $version_storage, ?Deployment_Config $deployment_config = null)
    {
        $this->app_state = $app_state;
        $this->version_storage = $version_storage;
        $this->deployment_config = $deployment_config ?: Object_Manager::get_instance()->get(Deployment_Config::class);
    }
    /**
     * Retrieve deployment version of static files
     *
     * @return string
     */
    public function get_value()
    {
        if (!$this->cached_value) {
            $this->cached_value = $this->read_value($this->app_state->get_mode());
        }
        return $this->cached_value;
    }
    /**
     * Load or generate deployment version of static files depending on the application mode
     *
     * @param string $appMode
     * @return string
     */
    protected function read_value($app_mode)
    {
        $result = $this->version_storage->load();
        if (!$result) {
            if ($app_mode == \Magento\Framework\App\State::MODE_PRODUCTION && !$this->deployment_config->get_config_data(Config_Options_List_Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
                $this->get_logger()->critical('Can not load static content version.');
                throw new \UnexpectedValueException('Unable to retrieve deployment version of static files from the file system.');
            }
            $result = $this->generate_version();
            $this->version_storage->save($result);
        }
        return $result;
    }
    /**
     * Generate version of static content
     *
     * @return int
     */
    private function generate_version()
    {
        return time();
    }
    /**
     * Get logger
     *
     * @return LoggerInterface
     * @deprecated 101.0.0
     */
    private function get_logger()
    {
        if ($this->logger == null) {
            $this->logger = \Magento\Framework\App\Object_Manager::get_instance()->get(Logger_Interface::class);
        }
        return $this->logger;
    }
}