<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

/**
 * List of bulk operations.
 */
class Operation_List implements \Magento\Asynchronous_Operations\Api\Data\Operation_List_Interface
{
    /**
     * @param array $items [optional]
     */
    public function __construct(private readonly array $items = [])
    {
    }
    /**
     * @inheritdoc
     */
    public function get_items()
    {
        return $this->items;
    }
}