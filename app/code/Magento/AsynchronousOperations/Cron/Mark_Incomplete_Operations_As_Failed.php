<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Cron;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation;
use Magento\Framework\Stdlib\DateTime\DateTime;
/**
 * Marks incomplete operations as failed
 */
class Mark_Incomplete_Operations_As_Failed
{
    /**
     * Default message maximum processing time. Default to 12h
     */
    private const DEFAULT_MESSAGE_MAX_PROCESSING_TIME = 43200;
    /**
     * Default error code
     */
    private const ERROR_CODE = 0;
    /**
     * Default error message
     */
    private const ERROR_MESSAGE = 'Unknown Error';
    public function __construct(private readonly Operation $resource, private readonly DateTime $date_time, private readonly int $message_max_processing_time = self::DEFAULT_MESSAGE_MAX_PROCESSING_TIME, private readonly int $failed_status = Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED, private readonly int $error_code = self::ERROR_CODE, private readonly string $error_message = self::ERROR_MESSAGE)
    {
    }
    /**
     * Marks incomplete operations as failed
     */
    public function execute(): void
    {
        $connection = $this->resource->get_connection();
        $now = $this->date_time->gmt_timestamp();
        $id_field = $this->resource->get_id_field_name();
        $select = $connection->select()->from($this->resource->get_main_table(), [$id_field])->where('status = ?', Operation_Interface::STATUS_TYPE_OPEN)->where('started_at <= ?', $connection->format_date($now - $this->message_max_processing_time));
        foreach ($connection->fetch_col($select) as $id) {
            $connection->update($this->resource->get_main_table(), ['status' => $this->failed_status, 'result_message' => $this->error_message, 'error_code' => $this->error_code], ["{$id_field} = ?" => (int) $id]);
        }
    }
}