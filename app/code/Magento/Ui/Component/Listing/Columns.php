<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Listing;

use Magento\Ui\Component\AbstractComponent;

/**
 * @api
 * @since 100.0.2
 */
class Columns extends AbstractComponent
{
    public const NAME = 'columns';

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
