<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Config\Source\Product;

/**
 * Catalog products per page on Grid mode source
 *
 */
class Thumbnail implements \Magento\Framework\Option\ArrayInterface
{
    public const OPTION_USE_PARENT_IMAGE = 'parent';

    public const OPTION_USE_OWN_IMAGE = 'itself';

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_USE_OWN_IMAGE, 'label' => __('Product Thumbnail Itself')],
            ['value' => self::OPTION_USE_PARENT_IMAGE, 'label' => __('Parent Product Thumbnail')],
        ];
    }
}
