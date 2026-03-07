<?php

declare(strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Content Disposition Source
 */
class Contentdisposition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'attachment', 'label' => __('attachment')],
            ['value' => 'inline', 'label' => __('inline')],
        ];
    }
}
