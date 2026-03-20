<?php

declare (strict_types=1);
/**
 * Logging schema locator
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Initial;

use Magento\Framework\Module\Dir;
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     */
    protected $_schema = null;
    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param string $moduleName
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $module_reader, $module_name)
    {
        $this->_schema = $module_reader->get_module_dir(Dir::MODULE_ETC_DIR, $module_name) . '/config.xsd';
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
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        return $this->_schema;
    }
}