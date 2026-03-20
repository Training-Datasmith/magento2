<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Async_Response_Interface;
use Magento\Asynchronous_Operations\Api\Data\Async_Response_Interface_Factory;
use Magento\Asynchronous_Operations\Api\Data\Item_Status_Interface;
use Magento\Asynchronous_Operations\Api\Data\Item_Status_Interface_Factory;
use Magento\Asynchronous_Operations\Api\Save_Multiple_Operations_Interface;
use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\Bulk\Bulk_Management_Interface;
use Magento\Framework\Data_Object\Identity_Generator_Interface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\Bulk_Exception;
use Magento\Framework\Exception\Localized_Exception;
use Psr\Log\Logger_Interface;
/**
 * Class MassSchedule used for adding multiple entities as Operations to Bulk Management with the status tracking
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Suppressed without refactoring to not introduce BiC
 */
class Mass_Schedule
{
    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $async_response_factory;
    /**
     * @var ItemStatusInterfaceFactory
     */
    private $item_status_interface_factory;
    /**
     * Initialize dependencies.
     */
    public function __construct(private readonly Identity_Generator_Interface $identity_service, Item_Status_Interface_Factory $item_status_interface_factory, Async_Response_Interface_Factory $async_response_factory, private readonly Bulk_Management_Interface $bulk_management, private readonly Logger_Interface $logger, private readonly Operation_Repository_Interface $operation_repository, private readonly User_Context_Interface $user_context, private readonly Encryptor $encryptor, private readonly Save_Multiple_Operations_Interface $save_multiple_operations)
    {
        $this->item_status_interface_factory = $item_status_interface_factory;
        $this->async_response_factory = $async_response_factory;
    }
    /**
     * Schedule new bulk operation based on the list of entities
     *
     * @param string $topicName
     * @param string $groupId
     * @param string $userId
     * @return AsyncResponseInterface
     * @throws BulkException
     * @throws LocalizedException
     */
    public function publish_mass($topic_name, array $entities_array, $group_id = null, $user_id = null)
    {
        $bulk_description = __('Topic %1', $topic_name);
        if ($user_id == null) {
            $user_id = $this->user_context->get_user_id();
        }
        if ($group_id == null) {
            $group_id = $this->identity_service->generate_id();
            /** create new bulk without operations */
            if (!$this->bulk_management->schedule_bulk($group_id, [], $bulk_description, $user_id)) {
                throw new Localized_Exception(__('Something went wrong while processing the request.'));
            }
        }
        $operations = [];
        $request_items = [];
        $bulk_exception = new Bulk_Exception();
        foreach ($entities_array as $key => $entity_params) {
            /** @var \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface $requestItem */
            $request_item = $this->item_status_interface_factory->create();
            try {
                $operation = $this->operation_repository->create($topic_name, $entity_params, $group_id, $key);
                $operations[] = $operation;
                $request_item->set_id($key);
                $request_item->set_status(Item_Status_Interface::STATUS_ACCEPTED);
                $request_item->set_data_hash($this->encryptor->hash($operation->get_serialized_data(), Encryptor::HASH_VERSION_SHA256));
                $request_items[] = $request_item;
            } catch (\Exception $exception) {
                $this->logger->error($exception);
                $request_item->set_id($key);
                $request_item->set_status(Item_Status_Interface::STATUS_REJECTED);
                $request_item->set_error_message($exception);
                $request_item->set_error_code($exception);
                $request_items[] = $request_item;
                $bulk_exception->add_exception(new Localized_Exception(__('Error processing %key element of input data', ['key' => $key]), $exception));
            }
        }
        if (!$this->bulk_management->schedule_bulk($group_id, $operations, $bulk_description, $user_id)) {
            try {
                $this->bulk_management->delete_bulk($group_id);
            } finally {
                throw new Localized_Exception(__('Something went wrong while processing the request.'));
            }
        }
        $this->save_multiple_operations->execute($operations);
        /** @var AsyncResponseInterface $asyncResponse */
        $async_response = $this->async_response_factory->create();
        $async_response->set_bulk_uuid($group_id);
        $async_response->set_request_items($request_items);
        if ($bulk_exception->was_error_added()) {
            $async_response->set_errors(true);
            $bulk_exception->add_data($async_response);
            throw $bulk_exception;
        }
        $async_response->set_errors(false);
        return $async_response;
    }
}