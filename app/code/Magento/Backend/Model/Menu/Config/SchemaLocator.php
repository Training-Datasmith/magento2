<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Config;

use Magento\Framework\Module\Dir;
/**
 * @api
 * @since 100.0.2
 */
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema = null;
    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_per_file_schema = null;
    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $module_reader)
    {
        $this->_schema = $module_reader->get_module_dir(Dir::MODULE_ETC_DIR, 'Magento_Backend') . '/menu.xsd';
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
        return $this->_per_file_schema;
    }
}