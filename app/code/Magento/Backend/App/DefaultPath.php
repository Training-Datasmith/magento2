<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

/**
 * Default application path for backend area
 *
 * @api
 * @since 100.0.2
 */
class Default_Path implements \Magento\Framework\App\Default_Path_Interface
{
    protected array $_parts;
    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(\Magento\Backend\App\Config_Interface $config)
    {
        $path_config_value = $config->get_value('web/default/admin') ?? '';
        $path_parts = [];
        if ($path_config_value) {
            $path_parts = explode('/', (string) $path_config_value);
        }
        $this->_parts = ['area' => $path_parts[0] ?? '', 'module' => $path_parts[1] ?? 'admin', 'controller' => $path_parts[2] ?? 'index', 'action' => $path_parts[3] ?? 'index'];
    }
    /**
     * Retrieve default path part by code
     *
     * @param string $code
     * @return string
     */
    public function get_part($code)
    {
        return $this->_parts[$code] ?? null;
    }
}