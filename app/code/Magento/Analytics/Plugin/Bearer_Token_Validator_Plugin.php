<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Plugin;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Validator\Bearer_Token_Validator;
/**
 * Overrides authorization config to always allow analytics token to be used as bearer
 */
class Bearer_Token_Validator_Plugin
{
    public function __construct(private readonly Scope_Config_Interface $config)
    {
    }
    /**
     * Always allow access token for analytics to be used as bearer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_is_integration_allowed_as_bearer_token(Bearer_Token_Validator $subject, bool $result, Integration $integration): bool
    {
        return $result || $integration->get_name() === $this->config->get_value('analytics/integration_name');
    }
}