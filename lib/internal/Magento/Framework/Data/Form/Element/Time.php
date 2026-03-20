<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Form time element
 */
class Time extends Abstract_Element
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
        $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer);
        $this->set_type('time');
        $this->secure_renderer = $secure_renderer;
    }
    /**
     * @inheritDoc
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
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_element_html()
    {
        $this->add_class('select admin__control-select');
        $this->add_class('select80wide');
        $value_hrs = 0;
        $value_min = 0;
        $value_sec = 0;
        if ($value = $this->get_value()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 3) {
                $value_hrs = $values[0];
                $value_min = $values[1];
                $value_sec = $values[2];
            }
        }
        $html = '<input type="hidden" id="' . $this->get_html_id() . '" ' . $this->_get_ui_id() . '/>';
        $html .= '<select name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id('hour') . '>' . "\n";
        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_hrs == $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";
        $html .= '<span class="time-separator">:&nbsp;</span><select name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id('minute') . '>' . "\n";
        for ($i = 0; $i < 60; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_min == $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";
        $html .= '<span class="time-separator">:&nbsp;</span><select name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id('second') . '>' . "\n";
        for ($i = 0; $i < 60; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_sec == $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";
        $html .= $this->get_after_element_html();
        $html .= $this->secure_renderer->render_tag('style', [], <<<style
                        .select80wide {
                            width: 80px !important;
                        }
        style, false);
        return $html;
    }
}