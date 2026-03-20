<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

class Schema extends \Magento\Framework\Data_Object
{
    /**
     * @param mixed $schema
     * @return void
     */
    public function load($schema)
    {
        if (is_array($schema)) {
            $this->set_data($schema);
        } elseif (is_string($schema)) {
            if (is_file($schema)) {
                include $schema;
                $this->set_data($schema);
            }
        }
    }
    /**
     * @param mixed $rawData
     * @return DataArray
     */
    public function extract($raw_data)
    {
        $elements = $raw_data;
        return new Data_Array($elements);
    }
}