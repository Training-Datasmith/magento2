<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Config\Model\Config as SystemConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;

/**
 * Manages the integration user at magento side.
 * User name stored in config.
 * User roles
 */
class IntegrationManager
{
    /**
     * IntegrationManager constructor
     */
    public function __construct(private readonly SystemConfig $config, private readonly IntegrationServiceInterface $integrationService, private readonly OauthServiceInterface $oauthService)
    {
    }

    /**
     * Activate predefined integration user
     *
     * @throws NoSuchEntityException
     */
    public function activateIntegration(): bool
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        if (!$integration->getId()) {
            throw new NoSuchEntityException(__('Cannot find predefined integration user!'));
        }
        $integrationData = $this->getIntegrationData(Integration::STATUS_ACTIVE);
        $integrationData['integration_id'] = $integration->getId();
        $this->integrationService->update($integrationData);
        return true;
    }

    /**
     * This method execute Generate Token command and enable integration
     *
     * @return bool|\Magento\Integration\Model\Oauth\Token
     */
    public function generateToken()
    {
        $consumerId = $this->generateIntegration()->getConsumerId();
        $accessToken = $this->oauthService->getAccessToken($consumerId);
        if (!$accessToken && $this->oauthService->createAccessToken($consumerId, true)) {
            return $this->oauthService->getAccessToken($consumerId);
        }
        return $accessToken;
    }

    /**
     * Returns consumer Id for MA integration user
     *
     * @return \Magento\Integration\Model\Integration
     */
    private function generateIntegration()
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        if (!$integration->getId()) {
            return $this->integrationService->create($this->getIntegrationData());
        }
        return $integration;
    }

    /**
     * Returns default attributes for MA integration user
     */
    private function getIntegrationData(int $status = Integration::STATUS_INACTIVE): array
    {
        return [
            'name' => $this->config->getConfigDataValue('analytics/integration_name'),
            'status' => $status,
            'all_resources' => false,
            'resource' => [
                'Magento_Analytics::analytics',
                'Magento_Analytics::analytics_api',
            ],
        ];
    }
}
