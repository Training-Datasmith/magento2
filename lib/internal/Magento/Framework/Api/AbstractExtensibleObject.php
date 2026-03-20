<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Base Class for extensible data Objects
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @deprecated 103.0.0
 * @see \Magento\Framework\Model\AbstractExtensibleModel
 * @since 100.0.2
 */
abstract class Abstract_Extensible_Object extends Abstract_Simple_Object implements Custom_Attributes_Data_Interface
{
    /**
     * Array key for custom attributes
     */
    public const CUSTOM_ATTRIBUTES_KEY = 'custom_attributes';
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extension_factory;
    /**
     * @var AttributeValueFactory
     */
    protected $attribute_value_factory;
    /**
     * @var string[]
     */
    protected $custom_attributes_codes;
    /**
     * Initialize internal storage
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param array $data
     */
    public function __construct(\Magento\Framework\Api\Extension_Attributes_Factory $extension_factory, Attribute_Value_Factory $attribute_value_factory, $data = [])
    {
        $this->extension_factory = $extension_factory;
        $this->attribute_value_factory = $attribute_value_factory;
        parent::__construct($data);
        if (isset($data[self::EXTENSION_ATTRIBUTES_KEY]) && is_array($data[self::EXTENSION_ATTRIBUTES_KEY])) {
            $this->populate_extension_attributes($data[self::EXTENSION_ATTRIBUTES_KEY]);
        }
    }
    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null null if the attribute has not been set
     */
    public function get_custom_attribute($attribute_code)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES]) && isset($this->_data[self::CUSTOM_ATTRIBUTES][$attribute_code]) ? $this->_data[self::CUSTOM_ATTRIBUTES][$attribute_code] : null;
    }
    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function get_custom_attributes()
    {
        return $this->_data[self::CUSTOM_ATTRIBUTES] ?? [];
    }
    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function set_custom_attributes(array $attributes)
    {
        $custom_attributes_codes = $this->get_custom_attributes_codes();
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Attribute_Value) {
                throw new \LogicException('Custom Attribute array elements can only be type of AttributeValue');
            }
            $attribute_code = $attribute->get_attribute_code();
            if (in_array($attribute_code, $custom_attributes_codes)) {
                $this->_data[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY][$attribute_code] = $attribute;
            }
        }
        return $this;
    }
    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function set_custom_attribute($attribute_code, $attribute_value)
    {
        $custom_attributes_codes = $this->get_custom_attributes_codes();
        /* If key corresponds to custom attribute code, populate custom attributes */
        if (in_array($attribute_code, $custom_attributes_codes)) {
            /** @var AttributeValue $attribute */
            $attribute = $this->attribute_value_factory->create();
            $attribute->set_attribute_code($attribute_code)->set_value($attribute_value);
            $this->_data[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY][$attribute_code] = $attribute;
        }
        return $this;
    }
    /**
     * Get a list of custom attribute codes.
     *
     * By default, entity can be extended only using extension attributes functionality.
     *
     * @return string[]
     */
    protected function get_custom_attributes_codes()
    {
        return $this->custom_attributes_codes ?? [];
    }
    /**
     * Receive a list of EAV attributes using provided metadata service.
     *
     * Can be used in child classes, which represent EAV entities.
     *
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @return string[]
     */
    protected function get_eav_attributes_codes(\Magento\Framework\Api\Metadata_Service_Interface $metadata_service)
    {
        $attribute_codes = [];
        $custom_attributes_metadata = $metadata_service->get_custom_attributes_metadata(get_class($this));
        if (is_array($custom_attributes_metadata)) {
            /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
            foreach ($custom_attributes_metadata as $attribute) {
                $attribute_codes[] = $attribute->get_attribute_code();
            }
        }
        return $attribute_codes;
    }
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected function _get_extension_attributes()
    {
        if (!$this->_get(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->populate_extension_attributes([]);
        }
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }
    /**
     * Instantiate extension attributes object and populate it with the provided data.
     *
     * @param array $extensionAttributesData
     * @return void
     */
    private function populate_extension_attributes(array $extension_attributes_data = [])
    {
        $extension_attributes = $this->extension_factory->create(get_class($this), $extension_attributes_data);
        $this->_set_extension_attributes($extension_attributes);
    }
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    protected function _set_extension_attributes(\Magento\Framework\Api\Extension_Attributes_Interface $extension_attributes)
    {
        $this->_data[self::EXTENSION_ATTRIBUTES_KEY] = $extension_attributes;
        return $this;
    }
}