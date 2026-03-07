<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class Link
 */
class Link extends AbstractComponent implements ControlInterface
{
    public const NAME = 'link';

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
