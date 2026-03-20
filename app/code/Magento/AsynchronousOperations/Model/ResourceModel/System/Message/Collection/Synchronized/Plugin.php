<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model\System\Message\Collection\Synchronized;

/**
 * Class Plugin to add bulks related notification messages to Synchronized Collection
 */
class Plugin
{
    private const MESSAGES_LIMIT = 5;
    /**
     * @var \Magento\AdminNotification\Model\System\MessageFactory
     */
    private $message_factory;
    /**
     * Plugin constructor.
     */
    public function __construct(\Magento\Admin_Notification\Model\System\Message_Factory $message_factory, private readonly \Magento\Framework\Bulk\Bulk_Status_Interface $bulk_status, private readonly \Magento\Asynchronous_Operations\Model\Bulk_Notification_Management $bulk_notification_management, private readonly \Magento\Authorization\Model\User_Context_Interface $user_context, private readonly \Magento\Asynchronous_Operations\Model\Operation\Details $operation_details, private readonly \Magento\Framework\Authorization_Interface $authorization, private readonly \Magento\Asynchronous_Operations\Model\Status_Mapper $status_mapper)
    {
        $this->message_factory = $message_factory;
    }
    /**
     * Adding bulk related messages to notification area
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_to_array(\Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection\Synchronized $collection, array $result): array
    {
        if (!$this->authorization->is_allowed('Magento_Logging::system_magento_logging_bulk_operations')) {
            return $result;
        }
        $user_id = $this->user_context->get_user_id();
        $user_bulks = $this->bulk_status->get_bulks_by_user($user_id);
        $acknowledged_bulks = $this->get_acknowledged_bulks_uuid($this->bulk_notification_management->get_acknowledged_bulks_by_user($user_id));
        $bulk_messages = [];
        $messages_count = 0;
        $data = [];
        foreach ($user_bulks as $bulk) {
            $bulk_uuid = $bulk->get_bulk_id();
            if (!in_array($bulk_uuid, $acknowledged_bulks)) {
                if ($messages_count < self::MESSAGES_LIMIT) {
                    $details = $this->operation_details->get_details($bulk_uuid);
                    $text = $this->get_text($details);
                    $bulk_status = $this->status_mapper->operation_status_to_bulk_summary_status($bulk->get_status());
                    if ($bulk_status === \Magento\Framework\Bulk\Bulk_Summary_Interface::IN_PROGRESS) {
                        $text = __('%1 item(s) are currently being updated.', $details['operations_total']) . $text;
                    }
                    $data = ['data' => [
                        'text' => __('Task "%1": ', $bulk->get_description()) . $text,
                        'severity' => \Magento\Framework\Notification\Message_Interface::SEVERITY_MAJOR,
                        // md5() here is not for cryptographic use.
                        // phpcs:ignore Magento2.Security.InsecureFunction
                        'identity' => md5('bulk' . $bulk_uuid),
                        'uuid' => $bulk_uuid,
                        'status' => $bulk_status,
                        'created_at' => $bulk->get_start_time(),
                    ]];
                    $messages_count++;
                }
                $bulk_messages[] = $this->message_factory->create($data)->to_array();
            }
        }
        if (!empty($bulk_messages)) {
            $result['totalRecords'] += count($bulk_messages);
            $bulk_messages = array_slice($bulk_messages, 0, 5);
            $result['items'] = array_merge($bulk_messages, $result['items']);
        }
        return $result;
    }
    /**
     * Get Bulk notification message
     *
     * @return \Magento\Framework\Phrase|string
     */
    private function get_text(array $operation_details)
    {
        if (0 == $operation_details['operations_successful'] && 0 == $operation_details['operations_failed']) {
            return __('%1 item(s) have been scheduled for update.', $operation_details['operations_total']);
        }
        $summary_report = '';
        if ($operation_details['operations_successful'] > 0) {
            $summary_report .= __('%1 item(s) have been successfully updated.', $operation_details['operations_successful']);
        }
        if ($operation_details['operations_failed'] > 0) {
            $summary_report .= '<strong>' . __('%1 item(s) failed to update', $operation_details['operations_failed']) . '</strong>';
        }
        return $summary_report;
    }
    /**
     * Get array with acknowledgedBulksUuid
     *
     * @param array $acknowledgedBulks
     */
    private function get_acknowledged_bulks_uuid($acknowledged_bulks): array
    {
        $acknowledged_bulks_array = [];
        foreach ($acknowledged_bulks as $bulk) {
            $acknowledged_bulks_array[] = $bulk->get_bulk_id();
        }
        return $acknowledged_bulks_array;
    }
}