<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

class Import
{
    public function __construct(private readonly \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry)
    {
    }

    /**
     * After import handler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAdvancedPricing(AdvancedPricing $subject): void
    {
        $this->invalidateIndexer();
    }

    /**
     * After delete handler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteAdvancedPricing(AdvancedPricing $subject): void
    {
        $this->invalidateIndexer();
    }

    /**
     * Invalidate indexer
     */
    private function invalidateIndexer(): void
    {
        $priceIndexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
        if (!$priceIndexer->isScheduled()) {
            $priceIndexer->invalidate();
        }
    }
}
