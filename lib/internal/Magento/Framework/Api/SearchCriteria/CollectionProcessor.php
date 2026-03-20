<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria;

use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
class Collection_Processor implements Collection_Processor_Interface
{
    /**
     * @var CollectionProcessorInterface[]
     */
    private $processors;
    /**
     * @param CollectionProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }
    /**
     * @inheritDoc
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection)
    {
        foreach ($this->processors as $name => $processor) {
            if (!$processor instanceof Collection_Processor_Interface) {
                throw new \InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $name, Collection_Processor_Interface::class));
            }
            $processor->process($search_criteria, $collection);
        }
    }
}