<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\AbstractProcessor;

/**
 * Class Processor
 * @api
 * @since 100.1.0
 */
class Processor extends AbstractProcessor
{
    /**
     * Indexer ID
     */
    public const INDEXER_ID = Fulltext::INDEXER_ID;
}
