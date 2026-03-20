<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Source\Option;

use Magento\Framework\Api\Attribute_Value_Factory;
use Magento\Framework\Api\Extension_Attributes_Factory;
class Type extends \Magento\Framework\Model\Abstract_Extensible_Model implements \Magento\Framework\Option\Array_Interface, \Magento\Bundle\Api\Data\Option_Type_Interface
{
    /**#@+
     * Constants
     */
    public const KEY_LABEL = 'label';
    public const KEY_CODE = 'code';
    /**#@-*/
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param array $options
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, Extension_Attributes_Factory $extension_factory, Attribute_Value_Factory $custom_attribute_factory, array $options, ?\Magento\Framework\Model\Resource_Model\Abstract_Resource $resource = null, ?\Magento\Framework\Data\Collection\Abstract_Db $resource_collection = null, array $data = [])
    {
        $this->options = $options;
        parent::__construct($context, $registry, $extension_factory, $custom_attribute_factory, $resource, $resource_collection, $data);
    }
    /**
     * Get Bundle Option Type
     *
     * @return array
     */
    public function to_option_array()
    {
        $types = [];
        foreach ($this->options as $value => $label) {
            $types[] = ['label' => $label, 'value' => $value];
        }
        return $types;
    }
    //@codeCoverageIgnoreStart
    /**
     * @inheritdoc
     */
    public function get_label()
    {
        return $this->get_data(self::KEY_LABEL);
    }
    /**
     * @inheritdoc
     */
    public function get_code()
    {
        return $this->get_data(self::KEY_CODE);
    }
    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label)
    {
        return $this->set_data(self::KEY_LABEL, $label);
    }
    /**
     * Set type code
     *
     * @param string $code
     * @return $this
     */
    public function set_code($code)
    {
        return $this->set_data(self::KEY_CODE, $code);
    }
    /**
     * @inheritdoc
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeExtensionInterface|null
     */
    public function get_extension_attributes()
    {
        return $this->_get_extension_attributes();
    }
    /**
     * @inheritdoc
     *
     * @param \Magento\Bundle\Api\Data\OptionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Option_Type_Extension_Interface $extension_attributes)
    {
        return $this->_set_extension_attributes($extension_attributes);
    }
    //@codeCoverageIgnoreEnd
}