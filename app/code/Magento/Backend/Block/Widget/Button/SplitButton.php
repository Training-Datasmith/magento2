<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Split button widget
 *
 * @method array getOptions()
 * @method string getButtonClass()
 * @method string getClass()
 * @method string getLabel()
 * @method string getTitle()
 * @method bool getDisabled()
 * @method string getStyle()
 * @method array getDataAttribute()
 * @api
 * @since 100.0.2
 */
class Split_Button extends \Magento\Backend\Block\Widget
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
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Context $context, array $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        parent::__construct($context, $data);
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
    }
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        if (!$this->has_template()) {
            $this->set_template('Magento_Backend::widget/button/split.phtml');
        }
        parent::_construct();
    }
    /**
     * Retrieve <div> wrapper attributes html
     *
     * @return string
     */
    public function get_attributes_html()
    {
        $title = $this->get_title();
        if (!$title) {
            $title = $this->get_label();
        }
        $classes = [];
        if ($this->has_split()) {
            $classes[] = 'actions-split';
        }
        //@TODO Perhaps use $this->getClass() instead
        if ($this->get_button_class()) {
            $classes[] = $this->get_button_class();
        }
        $attributes = ['id' => $this->get_id(), 'title' => $title, 'class' => join(' ', $classes)];
        $html = $this->_get_attributes_string($attributes);
        return $html;
    }
    /**
     * Get main button's "id" attribute value.
     *
     * @return string
     */
    private function get_button_id(): string
    {
        return $this->get_id() . '-button';
    }
    /**
     * Retrieve button attributes html
     *
     * @return string
     */
    public function get_button_attributes_html()
    {
        $disabled = $this->get_disabled() ? 'disabled' : '';
        $title = $this->get_title();
        if (!$title) {
            $title = $this->get_label();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'primary';
        // @TODO Perhaps use $this->getButtonClass() instead
        if ($this->get_class()) {
            $classes[] = $this->get_class();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = ['id' => $this->get_button_id(), 'title' => $title, 'class' => join(' ', $classes), 'disabled' => $disabled];
        //TODO perhaps we need to skip data-mage-init when disabled="disabled"
        if ($this->get_data_attribute()) {
            $this->_get_data_attributes($this->get_data_attribute(), $attributes);
        }
        $html = $this->_get_attributes_string($attributes);
        $html .= $this->get_ui_id();
        return $html;
    }
    /**
     * Retrieve toggle button attributes html
     *
     * @return string
     */
    public function get_toggle_attributes_html()
    {
        $disabled = $this->get_disabled() ? 'disabled' : '';
        $title = $this->get_title();
        if (!$title) {
            $title = $this->get_label();
        }
        $classes = [];
        $classes[] = 'action-toggle';
        $classes[] = 'primary';
        if ($this->get_class()) {
            $classes[] = $this->get_class();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = ['title' => $title, 'class' => join(' ', $classes), 'disabled' => $disabled, 'aria-label' => (string) $this->get_data('dropdown_button_aria_label')];
        $this->_get_data_attributes(['mage-init' => '{"dropdown": {}}', 'toggle' => 'dropdown'], $attributes);
        $html = $this->_get_attributes_string($attributes);
        $html .= $this->get_ui_id('dropdown');
        return $html;
    }
    /**
     * Retrieve options attributes html
     *
     * @param string $key
     * @param array $option
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_option_attributes_html($key, $option)
    {
        $disabled = isset($option['disabled']) && $option['disabled'] ? 'disabled' : '';
        if (isset($option['title'])) {
            $title = $option['title'];
        } else {
            $title = $option['label'];
        }
        $classes = [];
        $classes[] = 'item';
        if (!empty($option['default'])) {
            $classes[] = 'item-default';
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = $this->_prepare_option_attributes($option, $title, $classes, $disabled);
        $html = $this->_get_attributes_string($attributes);
        $html .= $this->get_ui_id(isset($option['id']) ? $option['id'] : 'item' . '-' . $key);
        return $html;
    }
    /**
     * Checks if the button needs actions-split functionality
     *
     * If this function returns false then split button will be rendered as simple button
     *
     * @return bool
     */
    public function has_split()
    {
        return $this->has_data('has_split') ? (bool) $this->get_data('has_split') : true;
    }
    /**
     * Add data attributes to $attributes array
     *
     * @param array $data
     * @param array $attributes
     * @return void
     */
    protected function _get_data_attributes($data, &$attributes)
    {
        foreach ($data as $key => $attr) {
            $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
        }
    }
    /**
     * Retrieve "id" attribute value for an option.
     *
     * @param array $option
     * @return string
     */
    private function identify_option(array $option): string
    {
        return isset($option['id']) ? $this->get_id() . '-' . $option['id'] : (isset($option['id_attribute']) ? $option['id_attribute'] : $this->get_id() . '-optId' . $this->random->get_random_string(10));
    }
    /**
     * Prepare option attributes
     *
     * @param array $option
     * @param string $title
     * @param string $classes
     * @param string $disabled
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepare_option_attributes($option, $title, $classes, $disabled)
    {
        $attributes = ['id' => $this->identify_option($option), 'title' => $title, 'class' => join(' ', $classes), 'disabled' => $disabled];
        if (isset($option['data_attribute'])) {
            $this->_get_data_attributes($option['data_attribute'], $attributes);
        }
        return $attributes;
    }
    /**
     * Render attributes array as attributes string
     *
     * @param array $attributes
     * @return string
     */
    protected function _get_attributes_string($attributes)
    {
        $html = [];
        foreach ($attributes as $attribute_key => $attribute_value) {
            if ($attribute_value === null || $attribute_value == '') {
                continue;
            }
            $html[] = $attribute_key . '="' . $this->escape_html_attr($attribute_value, false) . '"';
        }
        return join(' ', $html);
    }
    /**
     * @inheritDoc
     */
    protected function _before_to_html()
    {
        parent::_before_to_html();
        $after_html = $this->get_after_html();
        /** @var array|null $options */
        $options = $this->get_options() ?? [];
        foreach ($options as &$option) {
            $id = $option['id_attribute'] = $this->identify_option($option);
            if (!empty($option['onclick'])) {
                $after_html .= $this->secure_renderer->render_event_listener_as_tag('onclick', $option['onclick'], "#{$id}");
            }
            if (!empty($option['style'])) {
                $after_html .= $this->secure_renderer->render_style_as_tag($option['style'], "#{$id}");
            }
        }
        $this->set_options($options);
        $this->set_after_html($after_html);
        return $this;
    }
}