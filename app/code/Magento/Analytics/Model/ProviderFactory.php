<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for report providers
 */
class ProviderFactory
{
    public function __construct(private readonly ObjectManagerInterface $objectManager)
    {
    }

    /**
     * Object creation
     *
     * @param string $providerName
     * @return object
     */
    public function create($providerName)
    {
        return $this->objectManager->get($providerName);
    }
}
