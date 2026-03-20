<?php

declare (strict_types=1);
/**
 * Resource configuration schema locator
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection\Config;

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
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:App/etc/resources.xsd');
    }
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function get_schema()
    {
        return $this->get_per_file_schema();
    }
}