<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Api\Data;

/**
 * Represents link with collected data and initialized vector for decryption.
 *
 * @api
 */
interface Link_Interface
{
    /**
     * Retrieve url
     *
     * @return string
     */
    public function get_url();
    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function get_initialization_vector();
}