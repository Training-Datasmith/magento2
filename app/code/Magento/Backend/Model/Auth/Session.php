<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Auth;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Message\Manager_Interface;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory;
use Magento\Framework\Stdlib\Cookie_Manager_Interface;
/**
 * Backend Auth session model
 *
 * @api
 * @method \Magento\User\Model\User|null getUser()
 * @method \Magento\Backend\Model\Auth\Session setUser(\Magento\User\Model\User $value)
 * @method int getUpdatedAt()
 * @method \Magento\Backend\Model\Auth\Session setUpdatedAt(int $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @todo implement solution that keeps is_first_visit flag in session during redirects
 * @since 100.0.2
 */
class Session extends \Magento\Framework\Session\Session_Manager implements \Magento\Backend\Model\Auth\Storage_Interface
{
    /**
     * Admin session lifetime config path
     */
    public const XML_PATH_SESSION_LIFETIME = 'admin/security/session_lifetime';
    /**
     * @var boolean
     */
    protected $_is_first_after_login;
    /**
     * Access Control List builder
     *
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_acl_builder;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backend_url;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;
    /**
     * @var ManagerInterface
     */
    private $message_manager;
    /**
     * @var \Magento\Framework\Acl|null
     */
    private $acl = null;
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param ManagerInterface $messageManager
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request\Http $request, \Magento\Framework\Session\Sid_Resolver_Interface $sid_resolver, \Magento\Framework\Session\Config\Config_Interface $session_config, \Magento\Framework\Session\Save_Handler_Interface $save_handler, \Magento\Framework\Session\Validator_Interface $validator, \Magento\Framework\Session\Storage_Interface $storage, Cookie_Manager_Interface $cookie_manager, Cookie_Metadata_Factory $cookie_metadata_factory, \Magento\Framework\App\State $app_state, \Magento\Framework\Acl\Builder $acl_builder, \Magento\Backend\Model\Url_Interface $backend_url, \Magento\Backend\App\Config_Interface $config, ?Manager_Interface $message_manager = null)
    {
        $this->_config = $config;
        $this->_acl_builder = $acl_builder;
        $this->_backend_url = $backend_url;
        $this->message_manager = $message_manager ?? Object_Manager::get_instance()->get(Manager_Interface::class);
        parent::__construct($request, $sid_resolver, $session_config, $save_handler, $validator, $storage, $cookie_manager, $cookie_metadata_factory, $app_state);
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        parent::_reset_state();
        $this->_is_first_after_login = null;
        $this->acl = null;
    }
    /**
     * Refresh ACL resources stored in session
     *
     * @param  \Magento\User\Model\User $user
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function refresh_acl($user = null)
    {
        if ($user === null) {
            $user = $this->get_user();
        }
        if (!$user) {
            return $this;
        }
        if (!$this->get_acl() || $user->get_reload_acl_flag()) {
            $this->set_acl($this->_acl_builder->get_acl());
        }
        if ($user->get_reload_acl_flag()) {
            $user->unset_data('password');
            $user->set_reload_acl_flag(0)->save();
        }
        return $this;
    }
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function is_allowed($resource, $privilege = null)
    {
        $user = $this->get_user();
        $acl = $this->get_acl();
        if ($user && $acl) {
            try {
                return $acl->is_allowed($user->get_acl_role(), $resource, $privilege);
            } catch (\Exception $e) {
                try {
                    if (!$acl->has_resource($resource)) {
                        return $acl->is_allowed($user->get_acl_role(), null, $privilege);
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }
    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function is_logged_in()
    {
        return $this->get_user() && $this->get_user()->get_id();
    }
    /**
     * Set session UpdatedAt to current time
     *
     * @return void
     */
    public function prolong()
    {
        $session_user = $this->get_user();
        $error_message = '';
        if ($session_user !== null) {
            if ((int) $session_user->get_is_active() !== 1) {
                $error_message = 'The account sign-in was incorrect or your account is disabled temporarily. ' . 'Please wait and try again later.';
            }
            if (!$session_user->has_assigned2role($session_user->get_id())) {
                $error_message = 'More permissions are needed to access this.';
            }
            if (!empty($error_message)) {
                $this->destroy();
                $this->message_manager->add_error_message(__($error_message));
                return;
            }
        }
        $lifetime = $this->_config->get_value(self::XML_PATH_SESSION_LIFETIME);
        $cookie_value = $this->cookie_manager->get_cookie($this->get_name());
        if ($cookie_value) {
            $this->set_updated_at(time());
            $cookie_metadata = $this->cookie_metadata_factory->create_public_cookie_metadata()->set_duration($lifetime)->set_path($this->session_config->get_cookie_path())->set_domain($this->session_config->get_cookie_domain())->set_secure($this->session_config->get_cookie_secure())->set_http_only($this->session_config->get_cookie_http_only())->set_same_site($this->session_config->get_cookie_same_site());
            $this->cookie_manager->set_public_cookie($this->get_name(), $cookie_value, $cookie_metadata);
        }
    }
    /**
     * Check if it is the first page after successful login
     *
     * @return bool
     */
    public function is_first_page_after_login()
    {
        if ($this->_is_first_after_login === null) {
            $this->_is_first_after_login = $this->get_data('is_first_visit', true);
        }
        return $this->_is_first_after_login;
    }
    /**
     * Setter whether the current/next page should be treated as first page after login
     *
     * @param bool $value
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function set_is_first_page_after_login($value)
    {
        $this->_is_first_after_login = (bool) $value;
        return $this->set_is_first_visit($this->_is_first_after_login);
    }
    /**
     * Process of configuring of current auth storage when login was performed
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function process_login()
    {
        if ($this->get_user()) {
            $this->regenerate_id();
            if ($this->_backend_url->use_secret_key()) {
                $this->_backend_url->renew_secret_urls();
            }
            $this->set_is_first_page_after_login(true);
            $this->set_acl($this->_acl_builder->get_acl());
            $this->set_updated_at(time());
        }
        return $this;
    }
    /**
     * Process of configuring of current auth storage when logout was performed
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function process_logout()
    {
        $this->destroy();
        return $this;
    }
    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function is_valid_for_path($path)
    {
        return true;
    }
    /**
     * Set Acl model
     *
     * @return \Magento\Framework\Acl
     */
    public function get_acl()
    {
        return $this->acl;
    }
    /**
     * Retrieve Acl
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function set_acl(\Magento\Framework\Acl $acl)
    {
        $this->acl = $acl;
    }
    /**
     * @inheritdoc
     */
    public function get_data($key = '', $clear = false)
    {
        return $key === 'acl' ? $this->get_acl() : parent::get_data($key, $clear);
    }
}