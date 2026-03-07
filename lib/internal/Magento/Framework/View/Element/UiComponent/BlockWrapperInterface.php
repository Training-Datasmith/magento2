<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface BlockWrapperInterface
 *
 * @api
 */
interface BlockWrapperInterface extends UiComponentInterface
{
    /**
     * Get wrapped block
     *
     * @return BlockInterface
     */
    public function getBlock();
}
