<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model;

use Magento\Framework\Exception\Authentication_Exception;
use Magento\Framework\Exception\Plugin\Authentication_Exception as PluginAuthenticationException;
use Magento\Framework\Phrase;
/**
 * Backend Auth model
 *
 * @api
 * @since 100.0.2
 */
class Auth
{
    /**
     * @var \Magento\Backend\Model\Auth\StorageInterface
     */
    protected $_auth_storage;
    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credential_storage;
    /**
     * Helper data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backend_data;
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_core_config;
    /**
     * @var \Magento\Framework\Data\Collection\ModelFactory
     */
    protected $_model_factory;
    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Backend\Model\Auth\StorageInterface $authStorage
     * @param \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\Data\Collection\ModelFactory $modelFactory
     */
    public function __construct(\Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Backend\Helper\Data $backend_data, \Magento\Backend\Model\Auth\Storage_Interface $auth_storage, \Magento\Backend\Model\Auth\Credential\Storage_Interface $credential_storage, \Magento\Framework\App\Config\Scope_Config_Interface $core_config, \Magento\Framework\Data\Collection\Model_Factory $model_factory)
    {
        $this->_event_manager = $event_manager;
        $this->_backend_data = $backend_data;
        $this->_auth_storage = $auth_storage;
        $this->_credential_storage = $credential_storage;
        $this->_core_config = $core_config;
        $this->_model_factory = $model_factory;
    }
    /**
     * Set auth storage if it is instance of \Magento\Backend\Model\Auth\StorageInterface
     *
     * @param \Magento\Backend\Model\Auth\StorageInterface $storage
     * @return $this
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function set_auth_storage($storage)
    {
        if (!$storage instanceof \Magento\Backend\Model\Auth\Storage_Interface) {
            self::throw_exception(__('Authentication storage is incorrect.'));
        }
        $this->_auth_storage = $storage;
        return $this;
    }
    /**
     * Return auth storage.
     *
     * If auth storage was not defined outside - returns default object of auth storage
     *
     * @return \Magento\Backend\Model\Auth\StorageInterface
     * @codeCoverageIgnore
     */
    public function get_auth_storage()
    {
        return $this->_auth_storage;
    }
    /**
     * Return current (successfully authenticated) user,
     *
     * An instance of \Magento\Backend\Model\Auth\Credential\StorageInterface
     *
     * @return \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    public function get_user()
    {
        return $this->get_auth_storage()->get_user();
    }
    /**
     * Initialize credential storage from configuration
     *
     * @return void
     */
    protected function _init_credential_storage()
    {
        $this->_credential_storage = $this->_model_factory->create(\Magento\Backend\Model\Auth\Credential\Storage_Interface::class);
    }
    /**
     * Return credential storage object
     *
     * @return null|\Magento\Backend\Model\Auth\Credential\StorageInterface
     * @codeCoverageIgnore
     */
    public function get_credential_storage()
    {
        return $this->_credential_storage;
    }
    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throw_exception(__('The account sign-in was incorrect or your account is disabled temporarily. ' . 'Please wait and try again later.'));
        }
        try {
            $this->_init_credential_storage();
            $this->get_credential_storage()->login($username, $password);
            if ($this->get_credential_storage()->get_id()) {
                $this->get_auth_storage()->set_user($this->get_credential_storage());
                $this->get_auth_storage()->process_login();
                $this->_event_manager->dispatch('backend_auth_user_login_success', ['user' => $this->get_credential_storage()]);
            }
            if (!$this->get_auth_storage()->get_user()) {
                self::throw_exception(__('The account sign-in was incorrect or your account is disabled temporarily. ' . 'Please wait and try again later.'));
            }
        } catch (Plugin_Authentication_Exception $e) {
            $this->_event_manager->dispatch('backend_auth_user_login_failed', ['user_name' => $username, 'exception' => $e]);
            throw $e;
        } catch (\Magento\Framework\Exception\Localized_Exception $e) {
            $this->_event_manager->dispatch('backend_auth_user_login_failed', ['user_name' => $username, 'exception' => $e]);
            self::throw_exception(__($e->get_message() ?: 'The account sign-in was incorrect or your account is disabled temporarily. ' . 'Please wait and try again later.'));
        }
    }
    /**
     * Perform logout process
     *
     * @return void
     */
    public function logout()
    {
        $this->get_auth_storage()->process_logout();
    }
    /**
     * Check if current user is logged in
     *
     * @return bool
     */
    public function is_logged_in()
    {
        return $this->get_auth_storage()->is_logged_in();
    }
    /**
     * Throws specific Backend Authentication \Exception
     *
     * @param \Magento\Framework\Phrase $msg
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function throw_exception(?Phrase $msg = null)
    {
        if ($msg === null) {
            $msg = __('An authentication error occurred. Verify and try again.');
        }
        throw new Authentication_Exception($msg);
    }
}