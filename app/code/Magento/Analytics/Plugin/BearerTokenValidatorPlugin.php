<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Analytics\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Validator\BearerTokenValidator;

/**
 * Overrides authorization config to always allow analytics token to be used as bearer
 */
class BearerTokenValidatorPlugin
{
    public function __construct(private readonly ScopeConfigInterface $config)
    {
    }

    /**
     * Always allow access token for analytics to be used as bearer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsIntegrationAllowedAsBearerToken(
        BearerTokenValidator $subject,
        bool $result,
        Integration $integration
    ): bool {
        return $result || $integration->getName() === $this->config->getValue('analytics/integration_name');
    }
}
