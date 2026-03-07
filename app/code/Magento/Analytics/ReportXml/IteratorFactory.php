<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory to create a result iterator
 */
class IteratorFactory
{
    /**
     * @param string $defaultIteratorName
     */
    public function __construct(private readonly ObjectManagerInterface $objectManager, private $defaultIteratorName = \IteratorIterator::class)
    {
    }

    /**
     * Creates instance of the result iterator with the query result as an input
     * Result iterator can be changed through report configuration
     * <report name="reportName" iterator="Iterator\Class\Name">
     *     < ...
     * </report>
     * Uses IteratorIterator by default
     *
     * @param string|null $iteratorName
     * @return \IteratorIterator
     */
    public function create(\Traversable $result, $iteratorName = null)
    {
        return $this->objectManager->create(
            $iteratorName ?: $this->defaultIteratorName,
            [
                'iterator' => $result,
            ]
        );
    }
}
