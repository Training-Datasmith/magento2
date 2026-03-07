<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * @api
 * @since 100.0.2
 */
class Text extends AbstractDataType
{
    public const NAME = 'text';

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
