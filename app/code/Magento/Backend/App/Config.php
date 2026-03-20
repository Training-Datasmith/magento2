<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * Backend config accessor.
 */
class Config implements Config_Interface
{
    /**
     * @var array
     */
    private $data;
    public function __construct(protected \Magento\Framework\App\Config $app_config)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_value($path)
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }
        $config_path = Scope_Config_Interface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $config_path .= '/' . $path;
        }
        return $this->app_config->get(System::CONFIG_TYPE, $config_path);
    }
    /**
     * @inheritdoc
     */
    public function set_value($path, $value): void
    {
        $this->data[$path] = $value;
    }
    /**
     * @inheritdoc
     */
    public function is_set_flag($path): bool
    {
        $config_path = Scope_Config_Interface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $config_path .= '/' . $path;
        }
        return (bool) $this->app_config->get(System::CONFIG_TYPE, $config_path);
    }
}