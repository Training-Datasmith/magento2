<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface FileCollectorInterface
 *
 * @api
 */
interface FileCollectorInterface
{
    /**
     * Collect files
     *
     * @param string|null $searchPattern
     * @return array
     */
    public function collectFiles($searchPattern = null);
}
