<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object\Copy;

class Config
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config\Data
     */
    protected $_data_storage;
    /**
     * @param \Magento\Framework\DataObject\Copy\Config\Data $dataStorage
     */
    public function __construct(\Magento\Framework\Data_Object\Copy\Config\Data $data_storage)
    {
        $this->_data_storage = $data_storage;
    }
    /**
     * Get fieldsets by $path
     *
     * @param string $path
     * @return array
     */
    public function get_fieldsets($path)
    {
        return $this->_data_storage->get($path);
    }
    /**
     * Get the fieldset for an area
     *
     * @param string $name fieldset name
     * @param string $root fieldset area, could be 'admin'
     * @return null|array
     */
    public function get_fieldset($name, $root = 'global')
    {
        $fieldsets = $this->get_fieldsets($root);
        if (empty($fieldsets)) {
            return null;
        }
        return $fieldsets[$name] ?? null;
    }
}