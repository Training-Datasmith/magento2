<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface JsConfigInterface
 *
 * @api
 */
interface JsConfigInterface extends UiComponentInterface
{
    /**
     * Get configuration of related JavaScript Component
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getJsConfig(UiComponentInterface $component);
}
