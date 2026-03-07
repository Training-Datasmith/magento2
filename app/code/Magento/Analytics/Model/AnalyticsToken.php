<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Model for handling Magento BI token value into config.
 */
class AnalyticsToken
{
    /**
     * Path to value of Magento BI token into config.
     */
    private string $tokenPath = 'analytics/general/token';

    public function __construct(
        /**
         * Reinitable Config Model.
         */
        private readonly ReinitableConfigInterface $reinitableConfig,
        /**
         * Scope config model.
         */
        private readonly ScopeConfigInterface $config,
        /**
         * Service which allows to write values into config.
         */
        private readonly WriterInterface $configWriter
    ) {
    }

    /**
     * Get Magento BI token value.
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->config->getValue($this->tokenPath);
    }

    /**
     * Stores Magento BI token value.
     *
     * @param string $value
     */
    public function storeToken($value): bool
    {
        $this->configWriter->save($this->tokenPath, $value);
        $this->reinitableConfig->reinit();

        return true;
    }

    /**
     * Check Magento BI token value exist.
     */
    public function isTokenExist(): bool
    {
        return (bool)$this->getToken();
    }
}
