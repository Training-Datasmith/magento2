<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model;

use Magento\Catalog\Api\Data\Product_Interface;
/**
 * Composite processor to handle bundle product relations.
 */
class Product_Relations_Processor_Composite implements Product_Relations_Processor_Interface
{
    /**
     * @var ProductRelationsProcessorInterface[]
     */
    private $processors;
    /**
     * @param ProductRelationsProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof Product_Relations_Processor_Interface) {
                throw new \InvalidArgumentException(__('Product relations processor must implement %1.', Product_Relations_Processor_Interface::class));
            }
        }
        $this->processors = $processors;
    }
    /**
     * @inheritDoc
     */
    public function process(Product_Interface $product, array $existing_product_options, array $expected_product_options): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($product, $existing_product_options, $expected_product_options);
        }
    }
}