<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Data\Form\Element\Fieldset;
/**
 * Class ElementCreator
 *
 * @deprecated 101.0.1 in favour of UI component implementation
 * @package Magento\Backend\Block\Widget\Form\Element
 */
class Element_Creator
{
    /**
     * @var array
     */
    private $modifiers;
    /**
     * ElementCreator constructor.
     *
     * @param array $modifiers
     */
    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }
    /**
     * Creates element
     *
     * @param Fieldset $fieldset
     * @param Attribute $attribute
     *
     * @return AbstractElement
     */
    public function create(Fieldset $fieldset, Attribute $attribute): Abstract_Element
    {
        $config = $this->get_element_config($attribute);
        if (!empty($config['rendererClass'])) {
            $field_type = $config['inputType'] . '_' . $attribute->get_attribute_code();
            $fieldset->add_type($field_type, $config['rendererClass']);
        }
        return $fieldset->add_field($config['attribute_code'], $config['inputType'], $config)->set_entity_attribute($attribute);
    }
    /**
     * Returns element config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function get_element_config(Attribute $attribute): array
    {
        $default_config = $this->create_default_config($attribute);
        $config = $this->modify_config($default_config);
        $config['label'] = __($config['label']);
        return $config;
    }
    /**
     * Returns default config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function create_default_config(Attribute $attribute): array
    {
        return ['inputType' => $attribute->get_frontend()->get_input_type(), 'rendererClass' => $attribute->get_frontend()->get_input_renderer_class(), 'attribute_code' => $attribute->get_attribute_code(), 'name' => $attribute->get_attribute_code(), 'label' => $attribute->get_frontend()->get_label(), 'class' => $attribute->get_frontend()->get_class(), 'required' => $attribute->get_is_required(), 'note' => $attribute->get_note()];
    }
    /**
     *  Modify config
     *
     * @param array $config
     * @return array
     */
    private function modify_config(array $config): array
    {
        if ($this->is_modified($config['attribute_code'])) {
            return $this->apply_modifier($config);
        }
        return $config;
    }
    /**
     * Returns bool if attribute need to modify
     *
     * @param string $attribute_code
     * @return bool
     */
    private function is_modified($attribute_code): bool
    {
        return isset($this->modifiers[$attribute_code]);
    }
    /**
     * Apply modifier to config
     *
     * @param array $config
     * @return array
     */
    private function apply_modifier(array $config): array
    {
        $modified_config = $this->modifiers[$config['attribute_code']];
        foreach (array_keys($config) as $key) {
            if (isset($modified_config[$key])) {
                $config[$key] = $modified_config[$key];
            }
        }
        return $config;
    }
}