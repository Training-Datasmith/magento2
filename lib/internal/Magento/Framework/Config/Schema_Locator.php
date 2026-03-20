<?php

declare (strict_types=1);
/**
 * Menu configuration schema locator
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Dom\Urn_Resolver;
/**
 * Class SchemaLocator provides the information about xsd schema to be used for a configuration validation
 * Current class can be configured through di.xml
 * The default value of realPath variable contains information about view.xsd to keep the backward compatibility.
 */
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema = null;
    /**
     * SchemaLocator constructor.
     *
     * @param UrnResolver $urnResolver
     * @param string $realPath
     */
    public function __construct(Urn_Resolver $urn_resolver, $real_path = 'urn:magento:framework:Config/etc/view.xsd')
    {
        $this->schema = $urn_resolver->get_real_path($real_path);
    }
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function get_schema()
    {
        return $this->schema;
    }
    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        return $this->get_schema();
    }
}