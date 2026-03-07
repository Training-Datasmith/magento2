<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractComponent implements UiComponentInterface
{
    public const NAME = 'collection';

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
