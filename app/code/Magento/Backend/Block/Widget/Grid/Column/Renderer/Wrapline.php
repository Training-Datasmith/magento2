<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer line to wrap
 *
 * @api
 * @since 100.0.2
 */
class Wrapline extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Default max length of a line at one row
     *
     * @var integer
     */
    protected $_default_max_line_length = 60;
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Stdlib\String_Utils $string, array $data = [])
    {
        $this->string = $string;
        parent::__construct($context, $data);
    }
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $line = parent::_get_value($row);
        $wrapped_line = '';
        $line_length = $this->get_column()->get_data('lineLength') ? $this->get_column()->get_data('lineLength') : $this->_default_max_line_length;
        for ($i = 0, $n = floor($this->string->strlen($line) / $line_length); $i <= $n; $i++) {
            $wrapped_line .= $this->string->substr($line, $line_length * $i, $line_length) . '<br />';
        }
        return $wrapped_line;
    }
}