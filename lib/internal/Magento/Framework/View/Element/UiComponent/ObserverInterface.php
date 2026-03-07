<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ObserverInterface
 *
 * @api
 */
interface ObserverInterface
{
    /**
     * Update component according to $component
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function update(UiComponentInterface $component);
}
