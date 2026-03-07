<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\Analytics\ReportXml\DB\SelectBuilder
 */
class SelectBuilderFactory
{
    /**
     * SelectBuilderFactory constructor.
     */
    public function __construct(
        /**
         * Object Manager instance
         */
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * Create class instance with specified parameters
     *
     * @return SelectBuilder
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(SelectBuilder::class, $data);
    }
}
