<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Form Input/Output Escape HTML entities Filter
 */
namespace Magento\Framework\Data\Form\Filter;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
/**
 * EscapeHtml Form Filter Data
 */
class Escapehtml implements \Magento\Framework\Data\Form\Filter\Filter_Interface
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @param Escaper|null $escaper
     */
    public function __construct(?Escaper $escaper = null)
    {
        $this->escaper = $escaper ?? Object_Manager::get_instance()->get(Escaper::class);
    }
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function input_filter($value)
    {
        return $value;
    }
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function output_filter($value)
    {
        return $this->escaper->escape_html($value);
    }
}