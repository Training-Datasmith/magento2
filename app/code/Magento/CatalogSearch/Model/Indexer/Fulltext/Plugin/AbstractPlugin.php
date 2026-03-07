<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Abstract plugin for indexers
 */
abstract class AbstractPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int $productId
     * @return void
     */
    protected function reindexRow($productId)
    {
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     */
    protected function reindexList(array $productIds)
    {
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }
    }
}
