<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid select input column renderer
 *
 * @api
 * @since 100.0.2
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter, array $data = [])
    {
        $this->_converter = $converter;
        parent::__construct($context, $data);
    }
    /**
     * Get options from column
     *
     * @return array
     */
    protected function _get_options()
    {
        return $this->_converter->to_flat_array($this->get_column()->get_options());
    }
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $name = $this->get_column()->get_name() ? $this->get_column()->get_name() : $this->get_column()->get_id();
        $html = '<select name="' . $this->escape_html($name) . '" ' . $this->get_column()->get_validate_class() . '>';
        $value = $row->get_data($this->get_column()->get_index());
        foreach ($this->_get_options() as $val => $label) {
            $selected = $val == $value && $value !== null ? ' selected="selected"' : '';
            $html .= '<option value="' . $this->escape_html($val) . '"' . $selected . '>';
            $html .= $this->escape_html($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}