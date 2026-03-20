<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Form Input/Output Trim Filter
 */
namespace Magento\Framework\Data\Form\Filter;

class Trim implements \Magento\Framework\Data\Form\Filter\Filter_Interface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function input_filter($value)
    {
        return $value !== null ? trim($value, ' ') : '';
    }
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function output_filter($value)
    {
        return $value;
    }
}