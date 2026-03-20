<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory to create a result iterator
 */
class Iterator_Factory
{
    /**
     * @param string $defaultIteratorName
     */
    public function __construct(private readonly Object_Manager_Interface $object_manager, private $default_iterator_name = \Iterator_Iterator::class)
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
    public function create(\Traversable $result, $iterator_name = null)
    {
        return $this->object_manager->create($iterator_name ?: $this->default_iterator_name, ['iterator' => $result]);
    }
}