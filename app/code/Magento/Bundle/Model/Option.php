<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

/**
 * Bundle Option Model
 *
 * @api
 * @method int getParentId()
 * @method null|\Magento\Catalog\Model\Product[] getSelections()
 * @method Option setParentId(int $value)
 * @since 100.0.2
 */
class Option extends \Magento\Framework\Model\Abstract_Extensible_Model implements \Magento\Bundle\Api\Data\Option_Interface
{
    /**#@+
     * Constants
     */
    public const KEY_OPTION_ID = 'option_id';
    public const KEY_TITLE = 'title';
    public const KEY_REQUIRED = 'required';
    public const KEY_TYPE = 'type';
    public const KEY_POSITION = 'position';
    public const KEY_SKU = 'sku';
    public const KEY_PRODUCT_LINKS = 'product_links';
    /**#@-*/
    /**
     * @var null
     */
    protected $default_selection = null;
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\Resource_Model\Option::class);
        parent::_construct();
    }
    /**
     * Add selection to option
     *
     * @param \Magento\Catalog\Model\Product $selection
     * @return void
     */
    public function add_selection(\Magento\Catalog\Model\Product $selection)
    {
        if (!$this->has_data('selections')) {
            $this->set_data('selections', []);
        }
        $selections = $this->get_data('selections');
        $selections[] = $selection;
        $this->set_selections($selections);
    }
    /**
     * Check Is Saleable Option
     *
     * @return bool
     */
    public function is_saleable()
    {
        $saleable = false;
        $selections = $this->get_selections();
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection->is_saleable()) {
                    $saleable = true;
                    break;
                }
            }
        }
        return $saleable;
    }
    /**
     * Retrieve default Selection object
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function get_default_selection()
    {
        if (!$this->default_selection && $this->get_selections()) {
            foreach ($this->get_selections() as $selection) {
                if ($selection->get_is_default()) {
                    $this->default_selection = $selection;
                    break;
                }
            }
        }
        return $this->default_selection;
    }
    /**
     * Check is multi Option selection
     *
     * @return bool
     */
    public function is_multi_selection()
    {
        return $this->get_type() == 'checkbox' || $this->get_type() == 'multi';
    }
    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function get_searchable_data($product_id, $store_id)
    {
        return $this->_get_resource()->get_searchable_data($product_id, $store_id);
    }
    /**
     * Return selection by it's id
     *
     * @param int $selectionId
     * @return \Magento\Catalog\Model\Product|null
     */
    public function get_selection_by_id($selection_id)
    {
        $found_selection = null;
        foreach ($this->get_selections() as $selection) {
            if ($selection->get_selection_id() == $selection_id) {
                $found_selection = $selection;
                break;
            }
        }
        return $found_selection;
    }
    //@codeCoverageIgnoreStart
    /**
     * @inheritdoc
     */
    public function get_option_id()
    {
        return $this->get_data(self::KEY_OPTION_ID);
    }
    /**
     * @inheritdoc
     */
    public function get_title()
    {
        return $this->get_data(self::KEY_TITLE);
    }
    /**
     * @inheritdoc
     */
    public function get_required()
    {
        return $this->get_data(self::KEY_REQUIRED);
    }
    /**
     * @inheritdoc
     */
    public function get_type()
    {
        return $this->get_data(self::KEY_TYPE);
    }
    /**
     * @inheritdoc
     */
    public function get_position()
    {
        return $this->get_data(self::KEY_POSITION);
    }
    /**
     * @inheritdoc
     */
    public function get_sku()
    {
        return $this->get_data(self::KEY_SKU);
    }
    /**
     * @inheritdoc
     */
    public function get_product_links()
    {
        return $this->get_data(self::KEY_PRODUCT_LINKS);
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
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function set_title($title)
    {
        return $this->set_data(self::KEY_TITLE, $title);
    }
    /**
     * Set whether option is required
     *
     * @param bool $required
     * @return $this
     */
    public function set_required($required)
    {
        return $this->set_data(self::KEY_REQUIRED, $required);
    }
    /**
     * Set input type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type)
    {
        return $this->set_data(self::KEY_TYPE, $type);
    }
    /**
     * Set option position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position)
    {
        return $this->set_data(self::KEY_POSITION, $position);
    }
    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku)
    {
        return $this->set_data(self::KEY_SKU, $sku);
    }
    /**
     * Set product links
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $productLinks
     * @return $this
     */
    public function set_product_links(?array $product_links = null)
    {
        return $this->set_data(self::KEY_PRODUCT_LINKS, $product_links);
    }
    /**
     * @inheritdoc
     *
     * @return \Magento\Bundle\Api\Data\OptionExtensionInterface|null
     */
    public function get_extension_attributes()
    {
        return $this->_get_extension_attributes();
    }
    /**
     * @inheritdoc
     *
     * @param \Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Option_Extension_Interface $extension_attributes)
    {
        return $this->_set_extension_attributes($extension_attributes);
    }
    //@codeCoverageIgnoreEnd
}