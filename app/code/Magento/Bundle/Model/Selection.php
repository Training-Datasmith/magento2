<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

/**
 * Bundle Selection Model
 *
 * @method int getSelectionId()
 * @method \Magento\Bundle\Model\Selection setSelectionId(int $value)
 * @method int getOptionId()
 * @method \Magento\Bundle\Model\Selection setOptionId(int $value)
 * @method int getParentProductId()
 * @method \Magento\Bundle\Model\Selection setParentProductId(int $value)
 * @method int getProductId()
 * @method \Magento\Bundle\Model\Selection setProductId(int $value)
 * @method int getPosition()
 * @method \Magento\Bundle\Model\Selection setPosition(int $value)
 * @method int getIsDefault()
 * @method \Magento\Bundle\Model\Selection setIsDefault(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Bundle\Model\Selection setWebsiteId(int $value)
 * @method int getSelectionPriceType()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceType(int $value)
 * @method float getSelectionPriceValue()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceValue(float $value)
 * @method float getSelectionQty()
 * @method \Magento\Bundle\Model\Selection setSelectionQty(float $value)
 * @method int getSelectionCanChangeQty()
 * @method \Magento\Bundle\Model\Selection setSelectionCanChangeQty(int $value)
 * @api
 * @since 100.0.2
 */
class Selection extends \Magento\Framework\Model\Abstract_Model
{
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalog_data;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\ResourceModel\Selection $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Bundle\Model\Resource_Model\Selection $resource, ?\Magento\Framework\Data\Collection\Abstract_Db $resource_collection = null, array $data = [])
    {
        $this->_catalog_data = $catalog_data;
        parent::__construct($context, $registry, $resource, $resource_collection, $data);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\Resource_Model\Selection::class);
        parent::_construct();
    }
    /**
     * Processing object before save data
     *
     * @return void
     */
    public function before_save()
    {
        if (!$this->_catalog_data->is_price_global() && $this->get_website_id()) {
            $this->set_data('tmp_selection_price_value', $this->get_selection_price_value());
            $this->set_data('tmp_selection_price_type', $this->get_selection_price_type());
            $this->set_selection_price_value($this->get_orig_data('selection_price_value'));
            $this->set_selection_price_type($this->get_orig_data('selection_price_type'));
        }
        parent::before_save();
    }
    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function after_save()
    {
        if (!$this->_catalog_data->is_price_global() && $this->get_website_id()) {
            if (null !== $this->get_data('tmp_selection_price_value')) {
                $this->set_selection_price_value($this->get_data('tmp_selection_price_value'));
            }
            if (null !== $this->get_data('tmp_selection_price_type')) {
                $this->set_selection_price_type($this->get_data('tmp_selection_price_type'));
            }
            $this->get_resource()->save_selection_price($this);
            if (!$this->get_default_price_scope()) {
                $this->uns_selection_price_value();
                $this->uns_selection_price_type();
            }
        }
        return parent::after_save();
    }
}