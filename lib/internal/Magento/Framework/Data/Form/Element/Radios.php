<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Radio buttons collection
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data_Object;
use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Radio buttons form element widget.
 */
class Radios extends Abstract_Element
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?Secure_Html_Renderer $secure_renderer = null)
    {
        $this->secure_renderer = $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer);
        $this->set_type('radios');
    }
    /**
     * @inheritDoc
     */
    public function get_element_html()
    {
        $html = '';
        $value = $this->get_value();
        if ($values = $this->get_values()) {
            foreach ($values as $option) {
                $html .= $this->_option_to_html($option, $value);
            }
        }
        $html .= $this->get_after_element_html();
        return $html;
    }
    /**
     * Render choices.
     *
     * @param array $option
     * @param string[] $selected
     * @return string
     */
    protected function _option_to_html($option, $selected)
    {
        $html = '<div class="admin__field admin__field-option">' . '<input type="radio"' . $this->get_radio_button_attributes($option);
        if (is_array($option)) {
            $option = new Data_Object($option);
            $option_id = $this->get_html_id() . $option['value'];
            $html .= 'value="' . $this->_escape($option['value']) . '" class="admin__control-radio" id="' . $option_id . '"';
            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' . $this->get_html_id() . $option['value'] . '"><span>' . $option['label'] . '</span></label>';
        } elseif ($option instanceof Data_Object) {
            $option_id = $this->get_html_id() . $option->get_value();
            $html .= 'id="' . $option_id . '"' . $option->serialize(['label', 'title', 'value', 'class']);
            if (in_array($option->get_value(), $selected)) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="inline" for="' . $this->get_html_id() . $option->get_value() . '">' . $option->get_label() . '</label>';
        }
        if ($option->get_style()) {
            $html .= $this->secure_renderer->render_style_as_tag($option->get_style(), "#{$option_id}");
        }
        if ($option->get_onclick()) {
            $this->secure_renderer->render_event_listener_as_tag('onclick', $option->get_onclick(), "#{$option_id}");
        }
        if ($option->get_onchange()) {
            $this->secure_renderer->render_event_listener_as_tag('onchange', $option->get_onchange(), "#{$option_id}");
        }
        $html .= '</div>';
        return $html;
    }
    /**
     * @inheritDoc
     */
    public function get_html_attributes()
    {
        return array_merge(parent::get_html_attributes(), ['name']);
    }
    /**
     * Get a choice's HTML attributes.
     *
     * @param array $option
     * @return string
     */
    protected function get_radio_button_attributes($option)
    {
        $html = '';
        foreach ($this->get_html_attributes() as $attribute) {
            if ($value = $this->get_data_using_method($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" ';
            }
        }
        return $html;
    }
}