<?php

declare(strict_types=1);
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
class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    protected array $_parts;

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config)
    {
        $pathConfigValue = $config->getValue('web/default/admin') ?? '';
        $pathParts  = [];
        if ($pathConfigValue) {
            $pathParts = explode('/', (string) $pathConfigValue);
        }

        $this->_parts = [
            'area' => $pathParts[0] ?? '',
            'module' => $pathParts[1] ?? 'admin',
            'controller' => $pathParts[2] ?? 'index',
            'action' => $pathParts[3] ?? 'index',
        ];
    }

    /**
     * Retrieve default path part by code
     *
     * @param string $code
     * @return string
     */
    public function getPart($code)
    {
        return $this->_parts[$code] ?? null;
    }
}
