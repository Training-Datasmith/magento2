<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

class Config implements Config_Interface
{
    /**
     * @var \Magento\Framework\Cache\Config\Data
     */
    protected $_data_storage;
    /**
     * @param \Magento\Framework\Cache\Config\Data $dataStorage
     */
    public function __construct(\Magento\Framework\Cache\Config\Data $data_storage)
    {
        $this->_data_storage = $data_storage;
    }
    /**
     * @inheritDoc
     *
     * @return array
     */
    public function get_types()
    {
        return $this->_data_storage->get('types', []);
    }
    /**
     * @inheritDoc
     *
     * @param string $type
     * @return array
     */
    public function get_type($type)
    {
        return $this->_data_storage->get('types/' . $type, []);
    }
}