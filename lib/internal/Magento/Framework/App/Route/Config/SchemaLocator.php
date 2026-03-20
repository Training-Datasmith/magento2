<?php

declare (strict_types=1);
/**
 * Routes configuration schema locator
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route\Config;

class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urn_resolver;
    /**
     */
    public function __construct(\Magento\Framework\Config\Dom\Urn_Resolver $urn_resolver)
    {
        $this->urn_resolver = $urn_resolver;
    }
    /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function get_schema()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:App/etc/routes_merged.xsd');
    }
    /**
     * Get path to pre file validation schema
     *
     * @return string
     */
    public function get_per_file_schema()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:App/etc/routes.xsd');
    }
}