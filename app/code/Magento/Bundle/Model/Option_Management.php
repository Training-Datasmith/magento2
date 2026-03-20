<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

use Magento\Framework\Exception\Input_Exception;
class Option_Management implements \Magento\Bundle\Api\Product_Option_Management_Interface
{
    /**
     * @var \Magento\Bundle\Api\ProductOptionRepositoryInterface
     */
    protected $option_repository;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product_repository;
    /**
     * @param \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(\Magento\Bundle\Api\Product_Option_Repository_Interface $option_repository, \Magento\Catalog\Api\Product_Repository_Interface $product_repository)
    {
        $this->option_repository = $option_repository;
        $this->product_repository = $product_repository;
    }
    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Bundle\Api\Data\Option_Interface $option)
    {
        $product = $this->product_repository->get($option->get_sku(), true);
        if ($product->get_type_id() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('This is implemented for bundle products only.'));
        }
        return $this->option_repository->save($product, $option);
    }
}