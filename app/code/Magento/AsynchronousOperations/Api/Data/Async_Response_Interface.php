<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Interface AsyncResponseInterface
 * Temporary data object to give response from webapi async router
 *
 * @api
 * @since 100.2.3
 */
interface Async_Response_Interface
{
    public const BULK_UUID = 'bulk_uuid';
    public const REQUEST_ITEMS = 'request_items';
    public const ERRORS = 'errors';
    /**
     * Gets the bulk uuid.
     *
     * @return string Bulk Uuid.
     * @since 100.2.3
     */
    public function get_bulk_uuid();
    /**
     * Sets the bulk uuid.
     *
     * @param string $bulkUuid
     * @return $this
     * @since 100.2.3
     */
    public function set_bulk_uuid($bulk_uuid);
    /**
     * Gets the list of request items with status data.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[]
     * @since 100.2.3
     */
    public function get_request_items();
    /**
     * Sets the list of request items with status data.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[] $requestItems
     * @return $this
     * @since 100.2.3
     */
    public function set_request_items($request_items);
    /**
     * @param bool $isErrors
     * @return $this
     * @since 100.2.3
     */
    public function set_errors($is_errors = false);
    /**
     * Is there errors during processing bulk
     *
     * @return boolean
     * @since 100.2.3
     */
    public function is_errors();
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface|null
     * @since 100.2.3
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @return $this
     * @since 100.2.3
     */
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Async_Response_Extension_Interface $extension_attributes);
}