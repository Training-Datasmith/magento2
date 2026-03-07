<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component;

/**
 * Ui component DynamicRows
 * @api
 * @since 100.1.0
 */
class DynamicRows extends AbstractComponent
{
    public const NAME = 'dynamicRows';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
