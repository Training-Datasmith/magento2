<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Price
 */
class Price extends AbstractDataType
{
    public const NAME = 'price';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
