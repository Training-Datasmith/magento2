<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Form select element
 *
 * @api
 * @since 100.0.2
 */
class Select extends Abstract_Element
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
        $this->set_ext_type('combobox');
        $this->_prepare_options();
        $this->secure_renderer = $secure_renderer;
        $this->random = $random;
    }
    /**
     * Get the element Html.
     *
     * @return string
     */
    public function get_element_html()
    {
        $this->add_class('select admin__control-select');
        $html = '';
        if ($this->get_before_element_html()) {
            $html .= '<label class="addbefore" for="' . $this->get_html_id() . '">' . $this->get_before_element_html() . '</label>';
        }
        $html .= '<select id="' . $this->get_html_id() . '" name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id() . '>' . "\n";
        $value = $this->get_value();
        if (!is_array($value)) {
            $value = [$value];
        }
        if ($values = $this->get_values()) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html .= $this->_option_to_html(['value' => $key, 'label' => $option], $value);
                } elseif (is_array($option['value'])) {
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
        if ($this->get_after_element_html()) {
            $html .= '<label class="addafter" for="' . $this->get_html_id() . '">' . "\n{$this->get_after_element_html()}\n" . '</label>' . "\n";
        }
        return $html;
    }
    /**
     * Format an option as Html
     *
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _option_to_html($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="' . $option['label'] . '">' . "\n";
            foreach ($option['value'] as $group_item) {
                $html .= $this->_option_to_html($group_item, $selected);
            }
            $html .= '</optgroup>' . "\n";
        } else {
            $option_id = 'optId' . $this->random->get_random_string(8);
            $html = '<option value="' . $this->_escape($option['value']) . '" id="' . $option_id . '" ';
            $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
            if (in_array($option['value'], $selected)) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
            if (!empty($option['style'])) {
                $html .= $this->secure_renderer->render_style_as_tag($option['style'], "#{$option_id}");
            }
        }
        return $html;
    }
    /**
     * Prepare options.
     *
     * @return void
     */
    protected function _prepare_options()
    {
        $values = $this->get_values();
        if (empty($values)) {
            $options = $this->get_options();
            if (is_array($options)) {
                $values = [];
                foreach ($options as $value => $label) {
                    $values[] = ['value' => $value, 'label' => $label];
                }
            } elseif (is_string($options)) {
                $values = [['value' => $options, 'label' => $options]];
            }
            $this->set_values($values);
        }
    }
    /**
     * Get the Html attributes.
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex', 'data-form-part', 'data-role', 'data-action'];
    }
}