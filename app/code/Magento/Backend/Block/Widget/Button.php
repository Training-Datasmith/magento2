<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Button widget
 *
 * @api
 * @since 100.0.2
 */
class Button extends \Magento\Backend\Block\Widget
{
    /**
     * @var Random
     */
    private $random;
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @param Context $context
     * @param array $data
     * @param Random|null $random
     * @param SecureHtmlRenderer|null $htmlRenderer
     */
    public function __construct(Context $context, array $data = [], ?Random $random = null, ?Secure_Html_Renderer $html_renderer = null)
    {
        parent::__construct($context, $data);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
        $this->secure_renderer = $html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->set_template('Magento_Backend::widget/button.phtml');
        parent::_construct();
    }
    /**
     * Retrieve button type
     *
     * @return string
     */
    public function get_type()
    {
        if (in_array($this->get_data('type'), ['reset', 'submit'])) {
            return $this->get_data('type');
        }
        return 'button';
    }
    /**
     * Retrieve onclick handler
     *
     * @return null|string
     */
    public function get_on_click()
    {
        return $this->get_data('on_click') ?: $this->get_data('onclick');
    }
    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function get_attributes_html()
    {
        $disabled = $this->get_disabled() ? 'disabled' : '';
        $title = $this->get_title();
        if (!$title) {
            $title = $this->get_label();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'scalable';
        if ($this->get_class()) {
            $classes[] = $this->get_class();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        return $this->_attributes_to_html($this->_prepare_attributes($title, $classes, $disabled));
    }
    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepare_attributes($title, $classes, $disabled)
    {
        $attributes = ['id' => $this->get_id(), 'name' => $this->get_element_name(), 'title' => $title, 'type' => $this->get_type(), 'class' => join(' ', $classes), 'value' => $this->get_value(), 'disabled' => $disabled];
        if ($this->has_data('onclick_attribute')) {
            $attributes['onclick'] = $this->get_data('onclick_attribute');
        }
        if ($this->has_data('backend_button_widget_hook_id')) {
            $attributes['backend-button-widget-hook-id'] = $this->get_data('backend_button_widget_hook_id');
        }
        if ($this->get_data_attribute()) {
            foreach ($this->get_data_attribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }
    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function _attributes_to_html($attributes)
    {
        $html = '';
        foreach ($attributes as $attribute_key => $attribute_value) {
            if ($attribute_value === null || $attribute_value == '') {
                continue;
            }
            $html .= $attribute_key . '="' . $this->escape_html_attr($attribute_value, false) . '" ';
        }
        return $html;
    }
    /**
     * @inheritDoc
     */
    protected function _before_to_html()
    {
        parent::_before_to_html();
        $button_id = 'buttonId' . $this->random->get_random_string(10);
        $this->set_data('backend_button_widget_hook_id', $button_id);
        $after_html = $this->get_after_html();
        if ($this->get_on_click()) {
            $after_html .= $this->secure_renderer->render_event_listener_as_tag('onclick', $this->get_on_click(), "*[backend-button-widget-hook-id='{$button_id}']");
        }
        if ($this->get_style()) {
            $after_html .= $this->secure_renderer->render_style_as_tag($this->get_style(), "#{$this->get_id()}");
        }
        $this->set_after_html($after_html);
        return $this;
    }
}