<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Interface HttpRequestInterface
 *
 * @api
 */
interface Http_Request_Interface
{
    /**
     * Returned true if POST request
     *
     * @return boolean
     */
    public function is_post();
    /**
     * Returned true if GET request
     *
     * @return boolean
     */
    public function is_get();
    /**
     * Returned true if PATCH request
     *
     * @return boolean
     */
    public function is_patch();
    /**
     * Returned true if DELETE request
     *
     * @return boolean
     */
    public function is_delete();
    /**
     * Returned true if PUT request
     *
     * @return boolean
     */
    public function is_put();
    /**
     * Returned true if Ajax request
     *
     * @return boolean
     */
    public function is_ajax();
}