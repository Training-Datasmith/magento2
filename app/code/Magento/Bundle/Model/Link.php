<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

/**
 * Class Link
 * @codeCoverageIgnore
 */
class Link extends \Magento\Framework\Model\Abstract_Extensible_Model implements \Magento\Bundle\Api\Data\Link_Interface
{
    /**#@+
     * Constants
     */
    public const KEY_ID = 'id';
    public const KEY_SKU = 'sku';
    public const KEY_OPTION_ID = 'option_id';
    public const KEY_QTY = 'qty';
    public const KEY_POSITION = 'position';
    public const KEY_IS_DEFAULT = 'is_default';
    public const KEY_PRICE = 'price';
    public const KEY_PRICE_TYPE = 'price_type';
    public const KEY_CAN_CHANGE_QUANTITY = 'selection_can_change_quantity';
    /**#@-*/
    /**
     * {@inheritdoc}
     */
    public function get_id()
    {
        return $this->get_data(self::KEY_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function set_id($id)
    {
        return $this->set_data(self::KEY_ID, $id);
    }
    /**
     * {@inheritdoc}
     */
    public function get_sku()
    {
        return $this->get_data(self::KEY_SKU);
    }
    /**
     * {@inheritdoc}
     */
    public function get_option_id()
    {
        return $this->get_data(self::KEY_OPTION_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function get_qty()
    {
        return $this->get_data(self::KEY_QTY);
    }
    /**
     * {@inheritdoc}
     */
    public function get_position()
    {
        return $this->get_data(self::KEY_POSITION);
    }
    /**
     * {@inheritdoc}
     */
    public function get_is_default()
    {
        return $this->get_data(self::KEY_IS_DEFAULT);
    }
    /**
     * {@inheritdoc}
     */
    public function get_price()
    {
        return $this->get_data(self::KEY_PRICE);
    }
    /**
     * {@inheritdoc}
     */
    public function get_price_type()
    {
        return $this->get_data(self::KEY_PRICE_TYPE);
    }
    /**
     * {@inheritdoc}
     */
    public function get_can_change_quantity()
    {
        return $this->get_data(self::KEY_CAN_CHANGE_QUANTITY);
    }
    /**
     * Set linked product sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku)
    {
        return $this->set_data(self::KEY_SKU, $sku);
    }
    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function set_option_id($option_id)
    {
        return $this->set_data(self::KEY_OPTION_ID, $option_id);
    }
    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     */
    public function set_qty($qty)
    {
        return $this->set_data(self::KEY_QTY, $qty);
    }
    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position)
    {
        return $this->set_data(self::KEY_POSITION, $position);
    }
    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function set_is_default($is_default)
    {
        return $this->set_data(self::KEY_IS_DEFAULT, $is_default);
    }
    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price)
    {
        return $this->set_data(self::KEY_PRICE, $price);
    }
    /**
     * Set price type
     *
     * @param int $priceType
     * @return $this
     */
    public function set_price_type($price_type)
    {
        return $this->set_data(self::KEY_PRICE_TYPE, $price_type);
    }
    /**
     * Set whether quantity could be changed
     *
     * @param int $canChangeQuantity
     * @return $this
     */
    public function set_can_change_quantity($can_change_quantity)
    {
        return $this->set_data(self::KEY_CAN_CHANGE_QUANTITY, $can_change_quantity);
    }
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Bundle\Api\Data\LinkExtensionInterface|null
     */
    public function get_extension_attributes()
    {
        return $this->_get_extension_attributes();
    }
    /**
     * {@inheritdoc}
     *
     * @param \Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Link_Extension_Interface $extension_attributes)
    {
        return $this->_set_extension_attributes($extension_attributes);
    }
}