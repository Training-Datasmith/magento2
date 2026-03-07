<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * List of bulk operations.
 */
class OperationList implements \Magento\AsynchronousOperations\Api\Data\OperationListInterface
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
    public function getItems()
    {
        return $this->items;
    }
}
