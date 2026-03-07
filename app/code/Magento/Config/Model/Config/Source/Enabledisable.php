<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Config\Model\Config\Source;

/**
 * Source model for element with enable and disable variants.
 * @api
 * @since 100.0.2
 */
class Enabledisable implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Value which equal Enable for Enabledisable dropdown.
     */
    public const ENABLE_VALUE = 1;

    /**
     * Value which equal Disable for Enabledisable dropdown.
     */
    public const DISABLE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE_VALUE, 'label' => __('Enable')],
            ['value' => self::DISABLE_VALUE, 'label' => __('Disable')],
        ];
    }
}
