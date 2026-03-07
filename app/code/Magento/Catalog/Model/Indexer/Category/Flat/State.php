<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Indexer\Category\Flat;

/**
 * @api
 * @since 100.0.2
 */
class State extends \Magento\Catalog\Model\Indexer\AbstractFlatState
{
    /**
     * Indexer ID in configuration
     */
    public const INDEXER_ID = 'catalog_category_flat';

    /**
     * Flat Is Enabled Config XML Path
     */
    public const INDEXER_ENABLED_XML_PATH = 'catalog/frontend/flat_catalog_category';
}
