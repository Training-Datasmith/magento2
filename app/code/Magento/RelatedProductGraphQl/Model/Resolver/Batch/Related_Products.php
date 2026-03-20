<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\Resolver\Batch;

use Magento\Catalog\Model\Product\Link;

/**
 * Related Products Resolver
 */
class RelatedProducts extends AbstractLikedProducts
{
    /**
     * @inheritDoc
     */
    protected function getNode(): string
    {
        return 'related_products';
    }

    /**
     * @inheritDoc
     */
    protected function getLinkType(): int
    {
        return Link::LINK_TYPE_RELATED;
    }
}
