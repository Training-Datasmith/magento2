<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Data\Form\Element\Collection as ElementCollection;
use Magento\Framework\Data\Form\Element\Collection_Factory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Renderer\Renderer_Interface;
use Magento\Framework\Data\Form\Form_Key;
use Magento\Framework\Profiler;
/**
 * @api
 * @since 100.0.2
 */
class Form extends \Magento\Framework\Data\Form\Abstract_Form
{
    /**
     * All form elements collection
     *
     * @var ElementCollection
     */
    protected $_all_elements;
    /**
     * form elements index
     *
     * @var array
     */
    protected $_elements_index;
    /**
     * @var FormKey
     */
    protected $form_key;
    /**
     * @var RendererInterface
     */
    protected static $_default_element_renderer;
    /**
     * @var RendererInterface
     */
    protected static $_default_fieldset_renderer;
    /**
     * @var RendererInterface
     */
    protected static $_default_fieldset_element_renderer;
    /**
     * @param Factory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(Factory $factory_element, Element_Collection_Factory $factory_collection, Form_Key $form_key, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $data);
        $this->_all_elements = $this->_factory_collection->create(['container' => $this]);
        $this->form_key = $form_key;
    }
    /**
     * Method to set element renderer.
     *
     * @param RendererInterface|null $renderer
     *
     * @return void
     */
    public static function set_element_renderer(?Renderer_Interface $renderer = null)
    {
        self::$_default_element_renderer = $renderer;
    }
    /**
     * Method to set fieldset renderer.
     *
     * @param RendererInterface|null $renderer
     *
     * @return void
     */
    public static function set_fieldset_renderer(?Renderer_Interface $renderer = null)
    {
        self::$_default_fieldset_renderer = $renderer;
    }
    /**
     * Method to set fieldset element renderer.
     *
     * @param RendererInterface|null $renderer
     *
     * @return void
     */
    public static function set_fieldset_element_renderer(?Renderer_Interface $renderer = null)
    {
        self::$_default_fieldset_element_renderer = $renderer;
    }
    /**
     * Method to get element renderer.
     *
     * @return RendererInterface
     */
    public static function get_element_renderer()
    {
        return self::$_default_element_renderer;
    }
    /**
     * Method to get fieldset renderer.
     *
     * @return RendererInterface
     */
    public static function get_fieldset_renderer()
    {
        return self::$_default_fieldset_renderer;
    }
    /**
     * Method to get fieldset element renderer.
     *
     * @return RendererInterface
     */
    public static function get_fieldset_element_renderer()
    {
        return self::$_default_fieldset_element_renderer;
    }
    /**
     * Return allowed HTML form attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['id', 'name', 'method', 'action', 'enctype', 'class', 'onsubmit', 'target'];
    }
    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool $after
     * @return $this
     */
    public function add_element(Abstract_Element $element, $after = false)
    {
        $this->check_element_id($element->get_id());
        parent::add_element($element, $after);
        $this->add_element_to_collection($element);
        return $this;
    }
    /**
     * Check existing element
     *
     * @param   string $elementId
     * @return  bool
     */
    protected function _element_id_exists($element_id)
    {
        if ($element_id === null) {
            return false;
        }
        return isset($this->_elements_index[$element_id]);
    }
    /**
     * Method to add element to collection.
     *
     * @param AbstractElement $element
     *
     * @return $this
     */
    public function add_element_to_collection($element)
    {
        $element_id = $element->get_id();
        if ($element_id !== null) {
            $this->_elements_index[$element_id] = $element;
        }
        $this->_all_elements->add($element);
        return $this;
    }
    /**
     * Method to check element id.
     *
     * @param string $elementId
     *
     * @return bool
     * @throws \Exception
     */
    public function check_element_id($element_id)
    {
        if ($this->_element_id_exists($element_id)) {
            throw new \InvalidArgumentException('An element with a "' . $element_id . '" ID already exists.');
        }
        return true;
    }
    /**
     * Method to get form.
     *
     * @return $this
     */
    public function get_form()
    {
        return $this;
    }
    /**
     * Retrieve form element by id
     *
     * @param string $elementId
     * @return null|AbstractElement
     */
    public function get_element($element_id)
    {
        if ($this->_element_id_exists($element_id)) {
            return $this->_elements_index[$element_id];
        }
        return null;
    }
    /**
     * Method to set values.
     *
     * @param array $values
     *
     * @return $this
     */
    public function set_values($values)
    {
        foreach ($this->_all_elements as $element) {
            if (isset($values[$element->get_id()])) {
                $element->set_value($values[$element->get_id()]);
            } else {
                $element->set_value(null);
            }
        }
        return $this;
    }
    /**
     * Method to add values.
     *
     * @param array $values
     *
     * @return $this
     */
    public function add_values($values)
    {
        if (!is_array($values)) {
            return $this;
        }
        foreach ($values as $element_id => $value) {
            $element = $this->get_element($element_id);
            if ($element) {
                $element->set_value($value);
            }
        }
        return $this;
    }
    /**
     * Add suffix to name of all elements
     *
     * @param string $suffix
     * @return $this
     */
    public function add_field_name_suffix($suffix)
    {
        foreach ($this->_all_elements as $element) {
            $name = $element->get_name();
            if ($name) {
                $element->set_name($this->add_suffix_to_name($name, $suffix));
            }
        }
        return $this;
    }
    /**
     * Method to add suffix to name.
     *
     * @param string $name
     * @param string $suffix
     *
     * @return string
     */
    public function add_suffix_to_name($name, $suffix)
    {
        if (!$name) {
            return $suffix;
        }
        $vars = explode('[', $name);
        $new_name = $suffix;
        foreach ($vars as $index => $value) {
            $new_name .= '[' . $value;
            if ($index == 0) {
                $new_name .= ']';
            }
        }
        return $new_name;
    }
    /**
     * Method to remove field.
     *
     * @param string $elementId
     *
     * @return $this
     */
    public function remove_field($element_id)
    {
        if ($this->_element_id_exists($element_id)) {
            unset($this->_elements_index[$element_id]);
        }
        return $this;
    }
    /**
     * Method to set field container id prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function set_field_container_id_prefix($prefix)
    {
        $this->set_data('field_container_id_prefix', $prefix);
        return $this;
    }
    /**
     * Method to get field container id prefix.
     *
     * @return string
     */
    public function get_field_container_id_prefix()
    {
        return $this->get_data('field_container_id_prefix');
    }
    /**
     * Method to html.
     *
     * @return string
     */
    public function to_html()
    {
        Profiler::start('form/toHtml');
        $html = '';
        $use_container = $this->get_use_container();
        if ($use_container) {
            $html .= '<form ' . $this->serialize($this->get_html_attributes()) . '>';
            $html .= '<div>';
            $method = is_string($this->get_data('method')) ? strtolower($this->get_data('method')) : '';
            if ($method == 'post') {
                $html .= '<input name="form_key" type="hidden" value="' . $this->form_key->get_form_key() . '" />';
            }
            $html .= '</div>';
        }
        foreach ($this->get_elements() as $element) {
            $html .= $element->to_html();
        }
        if ($use_container) {
            $html .= '</form>';
        }
        Profiler::stop('form/toHtml');
        return $html;
    }
    /**
     * Method to get Html.
     *
     * @return string
     */
    public function get_html()
    {
        return $this->to_html();
    }
}