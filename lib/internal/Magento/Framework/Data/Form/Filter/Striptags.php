<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Form Input/Output Strip HTML tags Filter
 */
namespace Magento\Framework\Data\Form\Filter;

class Striptags implements \Magento\Framework\Data\Form\Filter\Filter_Interface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function input_filter($value)
    {
        return $value !== null ? strip_tags($value) : '';
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