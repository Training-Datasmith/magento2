<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Auth;

/**
 * Backend Auth Storage interface
 *
 * @api
 * @since 100.0.2
 */
interface Storage_Interface
{
    /**
     * Perform login specific actions
     *
     * @return $this
     * @abstract
     */
    public function process_login();
    /**
     * Perform logout specific actions
     *
     * @return $this
     * @abstract
     */
    public function process_logout();
    /**
     * Check if user is logged in
     *
     * @return bool
     * @abstract
     */
    public function is_logged_in();
    /**
     * Prolong storage lifetime
     *
     * @return void
     * @abstract
     */
    public function prolong();
}