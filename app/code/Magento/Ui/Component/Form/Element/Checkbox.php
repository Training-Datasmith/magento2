<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form\Element;

/**
 * Class Checkbox
 * @api
 * @since 100.0.2
 */
class Checkbox extends AbstractElement
{
    public const NAME = 'checkbox';

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
