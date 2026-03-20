<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface OperationInterface
 * @api
 * @since 103.0.0
 */
interface Operation_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ID = 'operation_key';
    public const BULK_ID = 'bulk_uuid';
    public const TOPIC_NAME = 'topic_name';
    public const SERIALIZED_DATA = 'serialized_data';
    public const RESULT_SERIALIZED_DATA = 'result_serialized_data';
    public const STATUS = 'status';
    public const RESULT_MESSAGE = 'result_message';
    public const ERROR_CODE = 'error_code';
    /**#@-*/
    /**#@+
     * Status types
     */
    public const STATUS_TYPE_COMPLETE = 1;
    public const STATUS_TYPE_RETRIABLY_FAILED = 2;
    public const STATUS_TYPE_NOT_RETRIABLY_FAILED = 3;
    public const STATUS_TYPE_OPEN = 4;
    public const STATUS_TYPE_REJECTED = 5;
    /**#@-*/
    /**
     * Operation id
     *
     * @return int
     * @since 103.0.0
     */
    public function get_id();
    /**
     * Set operation id
     *
     * @param int $id
     * @return $this
     * @since 103.0.0
     */
    public function set_id($id);
    /**
     * Get bulk uuid
     *
     * @return string
     * @since 103.0.0
     */
    public function get_bulk_uuid();
    /**
     * Set bulk uuid
     *
     * @param string $bulkId
     * @return $this
     * @since 103.0.0
     */
    public function set_bulk_uuid($bulk_id);
    /**
     * Message Queue Topic
     *
     * @return string
     * @since 103.0.0
     */
    public function get_topic_name();
    /**
     * Set message queue topic
     *
     * @param string $topic
     * @return $this
     * @since 103.0.0
     */
    public function set_topic_name($topic);
    /**
     * Serialized Data
     *
     * @return string
     * @since 103.0.0
     */
    public function get_serialized_data();
    /**
     * Set serialized data
     *
     * @param string $serializedData
     * @return $this
     * @since 103.0.0
     */
    public function set_serialized_data($serialized_data);
    /**
     * Result serialized Data
     *
     * @return string
     * @since 103.0.0
     */
    public function get_result_serialized_data();
    /**
     * Set result serialized data
     *
     * @param string $resultSerializedData
     * @return $this
     * @since 103.0.0
     */
    public function set_result_serialized_data($result_serialized_data);
    /**
     * Get operation status
     *
     * OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     *
     * @return int
     * @since 103.0.0
     */
    public function get_status();
    /**
     * Set status
     *
     * @param int $status
     * @return $this
     * @since 103.0.0
     */
    public function set_status($status);
    /**
     * Get result message
     *
     * @return string
     * @since 103.0.0
     */
    public function get_result_message();
    /**
     * Set result message
     *
     * @param string $resultMessage
     * @return $this
     * @since 103.0.0
     */
    public function set_result_message($result_message);
    /**
     * Get error code
     *
     * @return int
     * @since 103.0.0
     */
    public function get_error_code();
    /**
     * Set error code
     *
     * @param int $errorCode
     * @return $this
     * @since 103.0.0
     */
    public function set_error_code($error_code);
}