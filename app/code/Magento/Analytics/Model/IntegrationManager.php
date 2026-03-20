<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Config\Model\Config as SystemConfig;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Integration\Api\Integration_Service_Interface;
use Magento\Integration\Api\Oauth_Service_Interface;
use Magento\Integration\Model\Integration;
/**
 * Manages the integration user at magento side.
 * User name stored in config.
 * User roles
 */
class Integration_Manager
{
    /**
     * IntegrationManager constructor
     */
    public function __construct(private readonly System_Config $config, private readonly Integration_Service_Interface $integration_service, private readonly Oauth_Service_Interface $oauth_service)
    {
    }
    /**
     * Activate predefined integration user
     *
     * @throws NoSuchEntityException
     */
    public function activate_integration(): bool
    {
        $integration = $this->integration_service->find_by_name($this->config->get_config_data_value('analytics/integration_name'));
        if (!$integration->get_id()) {
            throw new No_Such_Entity_Exception(__('Cannot find predefined integration user!'));
        }
        $integration_data = $this->get_integration_data(Integration::STATUS_ACTIVE);
        $integration_data['integration_id'] = $integration->get_id();
        $this->integration_service->update($integration_data);
        return true;
    }
    /**
     * This method execute Generate Token command and enable integration
     *
     * @return bool|\Magento\Integration\Model\Oauth\Token
     */
    public function generate_token()
    {
        $consumer_id = $this->generate_integration()->get_consumer_id();
        $access_token = $this->oauth_service->get_access_token($consumer_id);
        if (!$access_token && $this->oauth_service->create_access_token($consumer_id, true)) {
            return $this->oauth_service->get_access_token($consumer_id);
        }
        return $access_token;
    }
    /**
     * Returns consumer Id for MA integration user
     *
     * @return \Magento\Integration\Model\Integration
     */
    private function generate_integration()
    {
        $integration = $this->integration_service->find_by_name($this->config->get_config_data_value('analytics/integration_name'));
        if (!$integration->get_id()) {
            return $this->integration_service->create($this->get_integration_data());
        }
        return $integration;
    }
    /**
     * Returns default attributes for MA integration user
     */
    private function get_integration_data(int $status = Integration::STATUS_INACTIVE): array
    {
        return ['name' => $this->config->get_config_data_value('analytics/integration_name'), 'status' => $status, 'all_resources' => false, 'resource' => ['Magento_Analytics::analytics', 'Magento_Analytics::analytics_api']];
    }
}