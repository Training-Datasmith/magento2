<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * ItemStatusInterface interface
 * Temporary object with status of requested item.
 * Indicate if entity param was Accepted|Rejected to bulk schedule
 *
 * @api
 * @since 100.2.3
 */
interface ItemStatusInterface
{
    public const ENTITY_ID = 'entity_id';
    public const DATA_HASH = 'data_hash';
    public const STATUS = 'status';
    public const ERROR_MESSAGE = 'error_message';
    public const ERROR_CODE = 'error_code';

    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get entity Id.
     *
     * @return int
     * @since 100.2.3
     */
    public function getId();

    /**
     * Sets entity Id.
     *
     * @param int $entityId
     * @return $this
     * @since 100.2.3
     */
    public function setId($entityId);

    /**
     * Get hash of entity data.
     *
     * @return string md5 hash of entity params array.
     * @since 100.2.3
     */
    public function getDataHash();

    /**
     * Sets hash of entity data.
     *
     * @param string $hash md5 hash of entity params array.
     * @return $this
     * @since 100.2.3
     */
    public function setDataHash($hash);

    /**
     * Get status.
     *
     * @return string accepted|rejected
     * @since 100.2.3
     */
    public function getStatus();

    /**
     * Sets entity status.
     *
     * @param string $status accepted|rejected
     * @return $this
     * @since 100.2.3
     */
    public function setStatus($status = self::STATUS_ACCEPTED);

    /**
     * Get error information.
     *
     * @return string|null
     * @since 100.2.3
     */
    public function getErrorMessage();

    /**
     * Sets error information.
     *
     * @param string|null|\Exception $error
     * @return $this
     * @since 100.2.3
     */
    public function setErrorMessage($error = null);

    /**
     * Get error code.
     *
     * @return int|null
     * @since 100.2.3
     */
    public function getErrorCode();

    /**
     * Sets error information.
     *
     * @param int|null|\Exception $errorCode Default: null
     * @return $this
     * @since 100.2.3
     */
    public function setErrorCode($errorCode = null);
}
