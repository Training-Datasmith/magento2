<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
/**
 * Extracts type ID for backpressure context
 */
interface Request_Type_Extractor_Interface
{
    /**
     * Extract type ID if possible
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @return string|null
     */
    public function extract(Request_Interface $request, Action_Interface $action): ?string;
}