<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Interface BulkSummaryInterface
 * @api
 * @since 100.2.0
 */
interface Bulk_Summary_Interface extends \Magento\Framework\Bulk\Bulk_Summary_Interface
{
    public const USER_TYPE = 'user_type';
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface|null
     * @since 100.2.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @return $this
     * @since 100.2.0
     */
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Extension_Interface $extension_attributes);
    /**
     * Get user type
     *
     * @return int
     * @since 100.3.0
     */
    public function get_user_type();
    /**
     * Set user type
     *
     * @param int $userType
     * @return $this
     * @since 100.3.0
     */
    public function set_user_type($user_type);
}