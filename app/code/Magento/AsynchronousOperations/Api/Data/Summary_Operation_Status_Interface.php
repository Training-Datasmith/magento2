<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Getter Class OperationsStatusInterface
 * Instead of OperationInterface this class don't provide all operation data
 * and not responsive to set any data, just to get operation data
 * without serialized_data and result_serialized_data
 *
 * @api
 * @since 100.2.3
 */
interface Summary_Operation_Status_Interface
{
    /**
     * Operation id
     *
     * @return int
     * @since 100.2.3
     */
    public function get_id();
    /**
     * Get operation status
     *
     * OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     *
     * @return int
     * @since 100.2.3
     */
    public function get_status();
    /**
     * Get result message
     *
     * @return string
     * @since 100.2.3
     */
    public function get_result_message();
    /**
     * Get error code
     *
     * @return int
     * @since 100.2.3
     */
    public function get_error_code();
}