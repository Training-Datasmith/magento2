<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * List of bulk operations. Used for mass save of operations via entity manager.
 * @api
 * @since 100.2.0
 */
interface Operation_List_Interface
{
    /**
     * Get list of operations.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     * @since 100.2.0
     */
    public function get_items();
}