<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid radiogroup column renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var int
     */
    protected $_default_width = 55;
    /**
     * @var array
     */
    protected $_values;
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
        parent::__construct($context, $data);
        $this->_converter = $converter;
    }
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _get_values()
    {
        $values = $this->get_column()->get_values();
        return $this->_converter->to_flat_array($values);
    }
    /**
     * Returns all values for the column
     *
     * @return array
     */
    public function get_values()
    {
        if ($this->_values === null) {
            $this->_values = $this->get_column()->get_data('values') ? $this->get_column()->get_data('values') : [];
        }
        return $this->_values;
    }
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $values = $this->_get_values();
        $value = $row->get_data($this->get_column()->get_index());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked = $value === $this->get_column()->get_value() ? ' checked="checked"' : '';
        }
        $html = '<input type="radio" name="' . $this->get_column()->get_html_name() . '" ';
        $html .= 'value="' . $row->get_id() . '" class="radio"' . $checked . '/>';
        return $html;
    }
}