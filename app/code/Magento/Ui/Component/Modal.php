<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component;

/**
 * @api
 * @since 100.1.0
 */
class Modal extends AbstractComponent
{
    public const NAME = 'modal';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
