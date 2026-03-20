<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api;

/**
 * Interface ProductOptionRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface Product_Option_Repository_Interface
{
    /**
     * Get option for bundle product
     *
     * @param string $sku
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($sku, $option_id);
    /**
     * Get all options for bundle product
     *
     * @param string $sku
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get_list($sku);
    /**
     * Remove bundle option
     *
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function delete(\Magento\Bundle\Api\Data\Option_Interface $option);
    /**
     * Remove bundle option
     *
     * @param string $sku
     * @param int $optionId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function delete_by_id($sku, $option_id);
    /**
     * Add new option for bundle product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(\Magento\Catalog\Api\Data\Product_Interface $product, \Magento\Bundle\Api\Data\Option_Interface $option);
}