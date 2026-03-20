<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

/**
 * Class Factory
 *
 * @api
 */
class Factory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function create(array $data = [])
    {
        return new \Magento\Framework\Data_Object($data);
    }
}