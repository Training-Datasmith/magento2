<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form select element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Multi-select form element widget.
 */
class Multiselect extends Abstract_Element
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $random = $random ?? Object_Manager::get_instance()->get(Random::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer, $random);
        $this->set_type('select');
        $this->set_ext_type('multiple');
        $this->set_size(10);
        $this->secure_renderer = $secure_renderer;
        $this->random = $random;
    }
    /**
     * Get the name
     *
     * @return string
     */
    public function get_name()
    {
        $name = parent::get_name();
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }
        return $name;
    }
    /**
     * Get the element as HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $this->add_class('select multiselect admin__control-multiselect');
        $html = '';
        if ($this->get_can_be_empty()) {
            $html .= '
                <input type="hidden" id="' . $this->get_html_id() . '_hidden" name="' . parent::get_name() . '" value="" />
                ';
        }
        if (!empty($this->_data['disabled'])) {
            $html .= '<input type="hidden" name="' . parent::get_name() . '_disabled" value="" />';
        }
        $html .= '<select id="' . $this->get_html_id() . '" name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id() . ' multiple="multiple">' . "\n";
        $value = $this->get_value();
        if (!is_array($value)) {
            $value = explode(',', $value ?? '');
        }
        $values = $this->get_values();
        if ($values) {
            foreach ($values as $option) {
                if (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $group_item) {
                        $html .= $this->_option_to_html($group_item, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_option_to_html($option, $value);
                }
            }
        }
        $html .= '</select>' . "\n";
        $html .= $this->get_after_element_html();
        return $html;
    }
    /**
     * Get the HTML attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'size', 'tabindex', 'data-form-part', 'data-role', 'data-action'];
    }
    /**
     * Get the default HTML
     *
     * @return string
     */
    public function get_default_html()
    {
        $result = $this->get_no_span() === true ? '' : '<span class="field-row">' . "\n";
        $result .= $this->get_label_html();
        $result .= $this->get_element_html();
        if ($this->get_select_all() && $this->get_deselect_all()) {
            $random = $this->random->get_random_string(4);
            $select_all_id = 'selId' . $random;
            $deselect_all_id = 'deselId' . $random;
            $result .= '<a href="#" id="' . $select_all_id . '">' . $this->get_select_all() . '</a> <span class="separator">&nbsp;|&nbsp;</span>';
            $result .= '<a href="#" id="' . $deselect_all_id . '">' . $this->get_deselect_all() . '</a>';
            $result .= $this->secure_renderer->render_event_listener_as_tag('onclick', "return {$this->get_js_object_name()}.selectAll();\nreturn false;", "#{$select_all_id}");
            $result .= $this->secure_renderer->render_event_listener_as_tag('onclick', "return {$this->get_js_object_name()}.deselectAll();", "#{$deselect_all_id}");
        }
        $result .= $this->get_no_span() === true ? '' : '</span>' . "\n";
        $script = '   var ' . $this->get_js_object_name() . ' = {' . "\n";
        $script .= '     selectAll: function() { ' . "\n";
        $script .= '         var sel = $("' . $this->get_html_id() . '");' . "\n";
        $script .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $script .= '             sel.options[i].selected = true; ' . "\n";
        $script .= '         } ' . "\n";
        $script .= '         return false; ' . "\n";
        $script .= '     },' . "\n";
        $script .= '     deselectAll: function() {' . "\n";
        $script .= '         var sel = $("' . $this->get_html_id() . '");' . "\n";
        $script .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $script .= '             sel.options[i].selected = false; ' . "\n";
        $script .= '         } ' . "\n";
        $script .= '         return false; ' . "\n";
        $script .= '     }' . "\n";
        $script .= '  }' . "\n";
        $result .= $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], $script, false);
        return $result;
    }
    /**
     * Get the  name of the JS object
     *
     * @return string
     */
    public function get_js_object_name()
    {
        return $this->get_html_id() . 'ElementControl';
    }
    /**
     * Render an option for the select.
     *
     * @param array $option
     * @param string[] $selected
     * @return string
     */
    protected function _option_to_html($option, $selected)
    {
        $option_id = 'optId' . $this->random->get_random_string(8);
        $html = '<option value="' . $this->_escape($option['value']) . '" id="' . $option_id . '" ';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        if (in_array((string) $option['value'], $selected)) {
            $html .= ' selected="selected"';
        }
        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        if (!empty($option['style'])) {
            $html .= $this->secure_renderer->render_style_as_tag($option['style'], "#{$option_id}");
        }
        return $html;
    }
}