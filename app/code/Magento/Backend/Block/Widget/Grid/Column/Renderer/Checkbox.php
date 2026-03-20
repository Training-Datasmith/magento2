<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Grid checkbox column renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
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
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Options\Converter $converter
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter, array $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        parent::__construct($context, $data);
        $this->_converter = $converter;
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
    }
    /**
     * Returns values of the column
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
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $values = $this->_get_values();
        $value = $row->get_data($this->get_column()->get_index());
        $checked = '';
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked_value = $this->get_column()->get_value();
            if ($checked_value !== null) {
                $checked = $value === $checked_value ? ' checked="checked"' : '';
            }
        }
        $disabled = '';
        $disabled_values = $this->get_column()->get_disabled_values();
        if (is_array($disabled_values)) {
            $disabled = in_array($value, $disabled_values) ? ' disabled="disabled"' : '';
        } else {
            $disabled_value = $this->get_column()->get_disabled_value();
            if ($disabled_value !== null) {
                $disabled = $value === $disabled_value ? ' disabled="disabled"' : '';
            }
        }
        $this->set_disabled($disabled);
        if ($this->get_no_object_id() || $this->get_column()->get_use_index()) {
            $v = $value;
        } else {
            $v = $row->get_id() != '' ? $row->get_id() : $value;
        }
        return $this->_get_checkbox_html($v, $checked);
    }
    /**
     * Render checkbox HTML.
     *
     * @param string $value   Value of the element
     * @param bool   $checked Whether it is checked
     * @return string
     */
    protected function _get_checkbox_html($value, $checked)
    {
        $html = '<label class="data-grid-checkbox-cell-inner" ';
        $html .= ' for="id_' . $this->escape_html($value) . '">';
        $html .= '<input type="checkbox" ';
        $html .= 'name="' . $this->get_column()->get_field_name() . '" ';
        $html .= 'value="' . $this->escape_html($value) . '" ';
        $html .= 'id="id_' . $this->escape_html($value) . '" ';
        $html .= 'class="' . ($this->get_column()->get_inline_css() ? $this->get_column()->get_inline_css() : 'checkbox') . ' admin__control-checkbox' . '"';
        $html .= $checked . $this->get_disabled() . '/>';
        $html .= '<label for="id_' . $this->escape_html($value) . '"></label>';
        $html .= '</label>';
        /* ToDo UI: add class="admin__field-label" after some refactoring _fields.less */
        return $html;
    }
    /**
     * Renders header of the column
     *
     * @return string
     */
    public function render_header()
    {
        if ($this->get_column()->get_header()) {
            return parent::render_header();
        }
        $checked = '';
        if ($filter = $this->get_column()->get_filter()) {
            $checked = $filter->get_value() ? ' checked="checked"' : '';
        }
        $disabled = '';
        if ($this->get_column()->get_disabled()) {
            $disabled = ' disabled="disabled"';
        }
        $id = 'id' . $this->random->get_random_string(10);
        $html = '<th class="data-grid-th data-grid-actions-cell"><input type="checkbox" ';
        $html .= 'id="' . $id . '" ';
        $html .= 'name="' . $this->get_column()->get_field_name() . '" ';
        $html .= 'class="admin__control-checkbox"' . $checked . $disabled . ' ';
        $html .= 'title="' . __('Select All') . '"/><label></label></th>';
        $html .= $this->secure_renderer->render_event_listener_as_tag('onclick', $this->get_column()->get_grid()->get_js_object_name() . '.checkCheckboxes(this)', "#{$id}");
        return $html;
    }
}