<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Exception;
use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface_Factory;
use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation\Collection;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation\Collection_Factory;
use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Bulk\Bulk_Management_Interface;
use Magento\Framework\Entity_Manager\Entity_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Message_Queue\Bulk_Publisher_Interface;
use Psr\Log\Logger_Interface;
use Throwable;
/**
 * Asynchronous Bulk Management
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bulk_Management implements Bulk_Management_Interface
{
    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulk_summary_factory;
    /**
     * @var CollectionFactory
     */
    private $operation_collection_factory;
    /**
     * BulkManagement constructor.
     */
    public function __construct(private readonly Entity_Manager $entity_manager, Bulk_Summary_Interface_Factory $bulk_summary_factory, Collection_Factory $operation_collection_factory, private readonly Bulk_Publisher_Interface $publisher, private readonly Metadata_Pool $metadata_pool, private readonly Resource_Connection $resource_connection, private readonly Logger_Interface $logger, private readonly User_Context_Interface $user_context)
    {
        $this->bulk_summary_factory = $bulk_summary_factory;
        $this->operation_collection_factory = $operation_collection_factory;
    }
    /**
     * @inheritDoc
     */
    public function schedule_bulk($bulk_uuid, array $operations, $description, $user_id = null): bool
    {
        $user_type = $this->user_context->get_user_type();
        if ($user_type === null) {
            $user_type = User_Context_Interface::USER_TYPE_ADMIN;
        }
        if ($user_id === null && $user_type === User_Context_Interface::USER_TYPE_ADMIN) {
            $user_id = $this->user_context->get_user_id();
        }
        $metadata = $this->metadata_pool->get_metadata(Bulk_Summary_Interface::class);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        // save bulk summary and related operations
        $connection->begin_transaction();
        try {
            /** @var BulkSummaryInterface $bulkSummary */
            $bulk_summary = $this->bulk_summary_factory->create();
            $this->entity_manager->load($bulk_summary, $bulk_uuid);
            $bulk_summary->set_bulk_id($bulk_uuid);
            $bulk_summary->set_description($description);
            $bulk_summary->set_user_id($user_id);
            $bulk_summary->set_user_type($user_type);
            $bulk_summary->set_operation_count((int) $bulk_summary->get_operation_count() + count($operations));
            $this->entity_manager->save($bulk_summary);
            $this->publish_operations($operations);
            $connection->commit();
        } catch (Exception $exception) {
            $connection->roll_back();
            $this->logger->critical($exception->get_message());
            return false;
        }
        return true;
    }
    /**
     * Retry bulk operations that failed due to given errors.
     *
     * @param string $bulkUuid target bulk UUID
     * @param array $errorCodes list of corresponding error codes
     * @return int number of affected bulk operations
     */
    public function retry_bulk($bulk_uuid, array $error_codes): int
    {
        /** @var Collection $collection */
        $collection = $this->operation_collection_factory->create();
        /** @var Operation[] $retriablyFailedOperations */
        $retriably_failed_operations = $collection->add_field_to_filter(Operation_Interface::BULK_ID, ['eq' => $bulk_uuid])->add_field_to_filter(Operation_Interface::ERROR_CODE, ['in' => $error_codes])->get_items();
        $affected_operations = count($retriably_failed_operations);
        if ($retriably_failed_operations) {
            $operation = reset($retriably_failed_operations);
            //async consumer expects operations to be in the database
            // thus such operation should not be deleted but reopened
            $should_reopen = str_starts_with($operation->get_topic_name() ?? '', Config_Interface::TOPIC_PREFIX);
            $metadata = $this->metadata_pool->get_metadata(Operation_Interface::class);
            $link_field = $metadata->get_link_field();
            $ids = [];
            foreach ($retriably_failed_operations as $operation) {
                $ids[] = (int) $operation->get_data($link_field);
            }
            $batch_size = 10000;
            $chunks = array_chunk($ids, $batch_size);
            $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
            $connection->begin_transaction();
            try {
                if ($should_reopen) {
                    foreach ($chunks as $chunk) {
                        $connection->update($metadata->get_entity_table(), [Operation_Interface::STATUS => Operation_Interface::STATUS_TYPE_OPEN, Operation_Interface::RESULT_SERIALIZED_DATA => null, Operation_Interface::ERROR_CODE => null, Operation_Interface::RESULT_MESSAGE => null, 'started_at' => null], [$link_field . ' IN (?)' => $chunk]);
                    }
                } else {
                    foreach ($chunks as $chunk) {
                        $connection->delete($metadata->get_entity_table(), [$link_field . ' IN (?)' => $chunk]);
                    }
                }
                $connection->commit();
            } catch (Throwable $exception) {
                $connection->roll_back();
                $this->logger->critical($exception->get_message());
                $affected_operations = 0;
            }
            if ($affected_operations) {
                $this->publish_operations($retriably_failed_operations);
            }
        }
        return $affected_operations;
    }
    /**
     * Publish list of operations to the corresponding message queues.
     */
    private function publish_operations(array $operations): void
    {
        $operations_by_topics = [];
        foreach ($operations as $operation) {
            $operations_by_topics[$operation->get_topic_name()][] = $operation;
        }
        foreach ($operations_by_topics as $topic_name => $operations) {
            $this->publisher->publish($topic_name, $operations);
        }
    }
    /**
     * @inheritDoc
     */
    public function delete_bulk($bulk_id)
    {
        return $this->entity_manager->delete($this->entity_manager->load($this->bulk_summary_factory->create(), $bulk_id));
    }
}