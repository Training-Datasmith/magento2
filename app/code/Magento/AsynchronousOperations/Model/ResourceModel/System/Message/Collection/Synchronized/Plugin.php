<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized;

/**
 * Class Plugin to add bulks related notification messages to Synchronized Collection
 */
class Plugin
{
    private const MESSAGES_LIMIT = 5;
    /**
     * @var \Magento\AdminNotification\Model\System\MessageFactory
     */
    private $messageFactory;

    /**
     * Plugin constructor.
     */
    public function __construct(
        \Magento\AdminNotification\Model\System\MessageFactory $messageFactory,
        private readonly \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus,
        private readonly \Magento\AsynchronousOperations\Model\BulkNotificationManagement $bulkNotificationManagement,
        private readonly \Magento\Authorization\Model\UserContextInterface $userContext,
        private readonly \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails,
        private readonly \Magento\Framework\AuthorizationInterface $authorization,
        private readonly \Magento\AsynchronousOperations\Model\StatusMapper $statusMapper
    ) {
        $this->messageFactory = $messageFactory;
    }

    /**
     * Adding bulk related messages to notification area
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToArray(
        \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized $collection,
        array $result
    ): array {
        if (!$this->authorization->isAllowed('Magento_Logging::system_magento_logging_bulk_operations')) {
            return $result;
        }
        $userId = $this->userContext->getUserId();
        $userBulks = $this->bulkStatus->getBulksByUser($userId);
        $acknowledgedBulks = $this->getAcknowledgedBulksUuid(
            $this->bulkNotificationManagement->getAcknowledgedBulksByUser($userId)
        );
        $bulkMessages = [];
        $messagesCount = 0;
        $data = [];
        foreach ($userBulks as $bulk) {
            $bulkUuid = $bulk->getBulkId();
            if (!in_array($bulkUuid, $acknowledgedBulks)) {
                if ($messagesCount < self::MESSAGES_LIMIT) {
                    $details = $this->operationDetails->getDetails($bulkUuid);
                    $text = $this->getText($details);
                    $bulkStatus = $this->statusMapper->operationStatusToBulkSummaryStatus($bulk->getStatus());
                    if ($bulkStatus === \Magento\Framework\Bulk\BulkSummaryInterface::IN_PROGRESS) {
                        $text = __('%1 item(s) are currently being updated.', $details['operations_total']) . $text;
                    }
                    $data = [
                        'data' => [
                            'text' => __('Task "%1": ', $bulk->getDescription()) . $text,
                            'severity' => \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR,
                            // md5() here is not for cryptographic use.
                            // phpcs:ignore Magento2.Security.InsecureFunction
                            'identity' => md5('bulk' . $bulkUuid),
                            'uuid' => $bulkUuid,
                            'status' => $bulkStatus,
                            'created_at' => $bulk->getStartTime(),
                        ],
                    ];
                    $messagesCount++;
                }
                $bulkMessages[] = $this->messageFactory->create($data)->toArray();
            }
        }

        if (!empty($bulkMessages)) {
            $result['totalRecords'] += count($bulkMessages);
            $bulkMessages = array_slice($bulkMessages, 0, 5);
            $result['items'] = array_merge($bulkMessages, $result['items']);
        }
        return $result;
    }

    /**
     * Get Bulk notification message
     *
     * @return \Magento\Framework\Phrase|string
     */
    private function getText(array $operationDetails)
    {
        if (0 == $operationDetails['operations_successful'] && 0 == $operationDetails['operations_failed']) {
            return __('%1 item(s) have been scheduled for update.', $operationDetails['operations_total']);
        }

        $summaryReport = '';
        if ($operationDetails['operations_successful'] > 0) {
            $summaryReport .= __(
                '%1 item(s) have been successfully updated.',
                $operationDetails['operations_successful']
            );
        }

        if ($operationDetails['operations_failed'] > 0) {
            $summaryReport .= '<strong>'
                . __('%1 item(s) failed to update', $operationDetails['operations_failed'])
                . '</strong>';
        }
        return $summaryReport;
    }

    /**
     * Get array with acknowledgedBulksUuid
     *
     * @param array $acknowledgedBulks
     */
    private function getAcknowledgedBulksUuid($acknowledgedBulks): array
    {
        $acknowledgedBulksArray = [];
        foreach ($acknowledgedBulks as $bulk) {
            $acknowledgedBulksArray[] = $bulk->getBulkId();
        }
        return $acknowledgedBulksArray;
    }
}
