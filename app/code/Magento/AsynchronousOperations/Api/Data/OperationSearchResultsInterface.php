<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Bulk operation search result interface.
 *
 * An bulk is a group of queue messages. An bulk operation item is a queue message.
 * @api
 * @since 100.3.0
 */
interface Operation_Search_Results_Interface extends \Magento\Framework\Api\Search_Results_Interface
{
    /**
     * Get list of operations.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     * @since 100.3.0
     */
    public function get_items();
    /**
     * Set list of operations.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface[] $items
     * @return $this
     * @since 100.3.0
     */
    public function set_items(array $items);
}