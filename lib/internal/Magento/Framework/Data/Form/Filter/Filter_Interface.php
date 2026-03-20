<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Form Input/Output Filter Interface
 */
namespace Magento\Framework\Data\Form\Filter;

/**
 * @api
 * @since 100.0.2
 */
interface Filter_Interface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function input_filter($value);
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function output_filter($value);
}