<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure;

use Magento\Framework\App\Request_Interface;
/**
 * Request context
 */
interface Context_Interface
{
    public const IDENTITY_TYPE_IP = 0;
    public const IDENTITY_TYPE_CUSTOMER = 1;
    public const IDENTITY_TYPE_ADMIN = 2;
    /**
     * Current request
     *
     * @return RequestInterface
     */
    public function get_request(): Request_Interface;
    /**
     * Unique ID for request issuer
     *
     * @return string
     */
    public function get_identity(): string;
    /**
     * Type of identity detected
     *
     * @return int
     */
    public function get_identity_type(): int;
    /**
     * Request type ID
     *
     * String ID of the functionality that requires backpressure enforcement
     *
     * @return string
     */
    public function get_type_id(): string;
}