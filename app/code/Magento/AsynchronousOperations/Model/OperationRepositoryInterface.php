<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
/**
 * Repository interface to create operation
 */
interface Operation_Repository_Interface
{
    /**
     * Create operation by topic, parameters and group ID
     *
     * @param string $topicName
     * @param array $entityParams
     * format: array(
     *     '<arg1-name>' => '<arg1-value>',
     *     '<arg2-name>' => '<arg2-value>',
     * )
     * @param string $groupId
     * @param int $operationId
     */
    public function create($topic_name, $entity_params, $group_id, $operation_id): Operation_Interface;
}