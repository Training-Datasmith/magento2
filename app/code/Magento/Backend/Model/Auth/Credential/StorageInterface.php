<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Auth\Credential;

/**
 * Backend Auth Credential Storage interface
 *
 * @api
 * @since 100.0.2
 */
interface Storage_Interface
{
    /**
     * Authenticate process.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate($username, $password);
    /**
     * Login action. Check if given username and password are valid
     *
     * @param string $username
     * @param string $password
     * @return $this
     * @abstract
     */
    public function login($username, $password);
    /**
     * Reload loaded (already authenticated) credential storage
     *
     * @return $this
     * @abstract
     */
    public function reload();
    /**
     * Check if user has available resources
     *
     * @return bool
     * @abstract
     */
    public function has_available_resources();
    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     * @abstract
     */
    public function set_has_available_resources($has_resources);
}