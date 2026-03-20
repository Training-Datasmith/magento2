<?php

declare (strict_types=1);
/**
 * Locator for fieldset XSD schemas.
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object\Copy\Config;

use Magento\Framework\Config\Dom\Urn_Resolver;
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;
    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_per_file_schema;
    /**
     * @param UrnResolver $urnResolver
     * @param string $schema
     * @param string $perFileSchema
     */
    public function __construct(Urn_Resolver $urn_resolver, $schema, $per_file_schema)
    {
        $this->_schema = $urn_resolver->get_real_path($schema);
        $this->_per_file_schema = $urn_resolver->get_real_path($per_file_schema);
    }
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function get_schema()
    {
        return $this->_schema;
    }
    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        return $this->_per_file_schema;
    }
}