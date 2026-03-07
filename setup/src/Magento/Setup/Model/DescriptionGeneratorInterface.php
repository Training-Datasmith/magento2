<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Model;

/**
 * Generate description for product
 */
interface DescriptionGeneratorInterface
{
    /**
     * Generate description per product net
     *
     * @param int $entityIndex
     * @return string
     */
    public function generate($entityIndex);
}
