<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\Quote\Address;

class FreeShipping implements \Magento\Quote\Model\Quote\Address\FreeShippingInterface
{
    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
        return false;
    }
}
