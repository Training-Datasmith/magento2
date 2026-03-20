<?php

declare (strict_types=1);
/**
 * Configuration Reinitable Interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface Reinitable_Config_Interface extends \Magento\Framework\App\Config\Mutable_Scope_Config_Interface
{
    /**
     * Reinitialize config object
     *
     * @return \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    public function reinit();
}