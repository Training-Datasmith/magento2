<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
/**
 * Bundle selection price factory
 * @api
 * @since 100.0.2
 */
class Bundle_Selection_Factory
{
    /**
     * Default selection class
     */
    public const SELECTION_CLASS_DEFAULT = \Magento\Bundle\Pricing\Price\Bundle_Selection_Price::class;
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create Price object for particular product
     *
     * @param Product $bundleProduct
     * @param Product $selection
     * @param float $quantity
     * @param array $arguments
     * @return BundleSelectionPrice
     */
    public function create(Product $bundle_product, Product $selection, $quantity, array $arguments = [])
    {
        $quantity = $quantity ? (float) $quantity : 1.0;
        $selection->set_qty($quantity);
        $arguments['bundleProduct'] = $bundle_product;
        $arguments['saleableItem'] = $selection;
        $arguments['quantity'] = $quantity;
        return $this->object_manager->create(self::SELECTION_CLASS_DEFAULT, $arguments);
    }
}