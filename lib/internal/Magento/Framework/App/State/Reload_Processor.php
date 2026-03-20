<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\State;

use Magento\Framework\App\Config\Scope_Code_Resolver;
use Magento\Framework\App\Deployment_Config;
/**
 * Framework specific reset state
 */
class Reload_Processor implements Reload_Processor_Interface
{
    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeCodeResolver $scopeCodeResolver
     */
    public function __construct(private readonly Deployment_Config $deployment_config, private readonly Scope_Code_Resolver $scope_code_resolver)
    {
    }
    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reload_state(): void
    {
        $this->deployment_config->reset_data();
        $this->scope_code_resolver->clean();
    }
}