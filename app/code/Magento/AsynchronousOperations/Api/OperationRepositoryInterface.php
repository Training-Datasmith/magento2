<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api;

/**
 * Bulk operation item repository interface.
 *
 * An bulk is a group of queue messages. An bulk operation item is a queue message.
 * @api
 * @since 100.3.0
 */
interface Operation_Repository_Interface
{
    /**
     * Lists the bulk operation items that match specified search criteria.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface
     * @since 100.3.0
     */
    public function get_list(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria);
}