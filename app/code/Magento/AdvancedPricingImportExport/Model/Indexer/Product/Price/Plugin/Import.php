<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Pricing_Import_Export\Model\Indexer\Product\Price\Plugin;

use Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing;
class Import
{
    public function __construct(private readonly \Magento\Framework\Indexer\Indexer_Registry $indexer_registry)
    {
    }
    /**
     * After import handler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_save_advanced_pricing(Advanced_Pricing $subject): void
    {
        $this->invalidate_indexer();
    }
    /**
     * After delete handler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_delete_advanced_pricing(Advanced_Pricing $subject): void
    {
        $this->invalidate_indexer();
    }
    /**
     * Invalidate indexer
     */
    private function invalidate_indexer(): void
    {
        $price_indexer = $this->indexer_registry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
        if (!$price_indexer->is_scheduled()) {
            $price_indexer->invalidate();
        }
    }
}