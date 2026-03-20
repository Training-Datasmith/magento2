<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config\Reader\Xml_Reader;

/**
 * Schema locator for Publishers
 */
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema;
    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $per_file_schema;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\Urn_Resolver $urn_resolver)
    {
        $this->schema = $urn_resolver->get_real_path('urn:magento:framework:Communication/etc/communication.xsd');
        $this->per_file_schema = $urn_resolver->get_real_path('urn:magento:framework:Communication/etc/communication.xsd');
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
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        return $this->per_file_schema;
    }
}