<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\Reinitable_Config_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
/**
 * Model for handling Magento BI token value into config.
 */
class Analytics_Token
{
    /**
     * Path to value of Magento BI token into config.
     */
    private string $token_path = 'analytics/general/token';
    public function __construct(
        /**
         * Reinitable Config Model.
         */
        private readonly Reinitable_Config_Interface $reinitable_config,
        /**
         * Scope config model.
         */
        private readonly Scope_Config_Interface $config,
        /**
         * Service which allows to write values into config.
         */
        private readonly Writer_Interface $config_writer
    )
    {
    }
    /**
     * Get Magento BI token value.
     *
     * @return string|null
     */
    public function get_token()
    {
        return $this->config->get_value($this->token_path);
    }
    /**
     * Stores Magento BI token value.
     *
     * @param string $value
     */
    public function store_token($value): bool
    {
        $this->config_writer->save($this->token_path, $value);
        $this->reinitable_config->reinit();
        return true;
    }
    /**
     * Check Magento BI token value exist.
     */
    public function is_token_exist(): bool
    {
        return (bool) $this->get_token();
    }
}