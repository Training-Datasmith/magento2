<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Asset;

/**
 * Interface ContentProcessorInterface
 *
 * @api
 */
interface ContentProcessorInterface
{
    /**
     * Error prefix
     */
    public const ERROR_MESSAGE_PREFIX = 'Compilation from source: ';

    /**
     * Process file content
     *
     * @param File $asset
     * @return string
     */
    public function processContent(File $asset);
}
