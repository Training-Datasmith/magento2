<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.1.0
 */
class ActionDelete extends AbstractElement
{
    public const NAME = 'actionDelete';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
