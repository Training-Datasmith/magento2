<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Framework\App\Object_Manager;
/**
 * Backend form widget
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @see MAGETWO-69846
 * @since 100.0.2
 */
class Form extends \Magento\Backend\Block\Widget
{
    /**
     * Form Object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form.phtml';
    /** @var Form\Element\ElementCreator */
    private $creator;
    /**
     * Constructs form
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param Form\Element\ElementCreator|null $creator
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [], ?Form\Element\Element_Creator $creator = null)
    {
        parent::__construct($context, $data);
        $this->creator = $creator ?: Object_Manager::get_instance()->get(Form\Element\Element_Creator::class);
    }
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_dest_element_id('edit_form');
    }
    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        \Magento\Framework\Data\Form::set_element_renderer($this->get_layout()->create_block(\Magento\Backend\Block\Widget\Form\Renderer\Element::class, $this->get_name_in_layout() . '_element'));
        \Magento\Framework\Data\Form::set_fieldset_renderer($this->get_layout()->create_block(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class, $this->get_name_in_layout() . '_fieldset'));
        \Magento\Framework\Data\Form::set_fieldset_element_renderer($this->get_layout()->create_block(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element::class, $this->get_name_in_layout() . '_fieldset_element'));
        return parent::_prepare_layout();
    }
    /**
     * Get form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function get_form()
    {
        return $this->_form;
    }
    /**
     * Get form HTML
     *
     * @return string
     */
    public function get_form_html()
    {
        if (is_object($this->get_form())) {
            return $this->get_form()->get_html();
        }
        return '';
    }
    /**
     * Set form object
     *
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    public function set_form(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->set_parent($this);
        $this->_form->set_base_url($this->_url_builder->get_base_url());
        $custom_attributes = $this->get_data('custom_attributes');
        if (is_array($custom_attributes)) {
            foreach ($custom_attributes as $key => $value) {
                $this->_form->add_custom_attribute($key, $value);
            }
        }
        return $this;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepare_form()
    {
        return $this;
    }
    /**
     * This method is called before rendering HTML
     *
     * @return $this
     */
    protected function _before_to_html()
    {
        $this->_prepare_form();
        $this->_init_form_values();
        return parent::_before_to_html();
    }
    /**
     * Initialize form fields values
     *
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return $this
     */
    protected function _init_form_values()
    {
        return $this;
    }
    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     * @return void
     */
    protected function _set_fieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_add_element_types($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if (!$this->_is_attribute_visible($attribute)) {
                continue;
            }
            if (($input_type = $attribute->get_frontend()->get_input_type()) && !in_array($attribute->get_attribute_code(), $exclude) && ('media_image' !== $input_type || $attribute->get_attribute_code() == 'image')) {
                $element = $this->creator->create($fieldset, $attribute);
                $element->set_after_element_html($this->_get_additional_element_html($element));
                $this->_apply_type_specific_config($input_type, $element, $attribute);
            }
        }
    }
    /**
     * Check whether attribute is visible
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return bool
     */
    protected function _is_attribute_visible(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        return !(!$attribute || $attribute->has_is_visible() && !$attribute->get_is_visible());
    }
    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return void
     */
    protected function _apply_type_specific_config($input_type, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        switch ($input_type) {
            case 'select':
                $element->set_values($attribute->get_source()->get_all_options(true, true));
                break;
            case 'multiselect':
                $element->set_values($attribute->get_source()->get_all_options(false, true));
                $element->set_can_be_empty(true);
                break;
            case 'date':
                $element->set_date_format($this->_locale_date->get_date_format_with_long_year());
                break;
            case 'datetime':
                $element->set_date_format($this->_locale_date->get_date_format_with_long_year());
                $element->set_time_format($this->_locale_date->get_time_format());
                break;
            case 'multiline':
                $element->set_line_count($attribute->get_multiline_count());
                break;
            default:
                break;
        }
    }
    /**
     * Add new element type
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $baseElement
     * @return void
     */
    protected function _add_element_types(\Magento\Framework\Data\Form\Abstract_Form $base_element)
    {
        $types = array_merge(['datetime' => 'date'], $this->_get_additional_element_types());
        foreach ($types as $code => $class_name) {
            $base_element->add_type($code, $class_name);
        }
    }
    /**
     * Retrieve predefined additional element types
     *
     * @return array
     */
    protected function _get_additional_element_types()
    {
        return [];
    }
    /**
     * Render additional element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _get_additional_element_html($element)
    {
        return '';
    }
}