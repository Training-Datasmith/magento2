<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\Url_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\Authorization_Interface;
use Magento\Framework\Data\Form\Form_Key\Validator as FormKeyValidator;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Locale\Resolver_Interface;
use Magento\Framework\View\Element\Abstract_Block;
/**
 * Generic backend controller
 *
 * @deprecated 102.0.0 Use \Magento\Framework\App\ActionInterface
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class Abstract_Action extends \Magento\Framework\App\Action\Action
{
    /**
     * Name of "is URLs checked" flag
     */
    public const FLAG_IS_URLS_CHECKED = 'check_url_settings';
    /**
     * Session namespace to refer in other places
     */
    public const SESSION_NAMESPACE = 'adminhtml';
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_public_actions = [];
    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_session_namespace = self::SESSION_NAMESPACE;
    /**
     * @var BackendHelper
     */
    protected $_helper;
    /**
     * @var Session
     */
    protected $_session;
    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;
    /**
     * @var Auth
     */
    protected $_auth;
    /**
     * @var UrlInterface
     */
    protected $_backend_url;
    /**
     * @var ResolverInterface
     */
    protected $_locale_resolver;
    /**
     * @var bool
     */
    protected $_can_use_base_url;
    /**
     * @var FormKeyValidator
     */
    protected $_form_key_validator;
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_authorization = $context->get_authorization();
        $this->_auth = $context->get_auth();
        $this->_helper = $context->get_helper();
        $this->_backend_url = $context->get_backend_url();
        $this->_form_key_validator = $context->get_form_key_validator();
        $this->_locale_resolver = $context->get_locale_resolver();
        $this->_can_use_base_url = $context->get_can_use_base_url();
        $this->_session = $context->get_session();
    }
    /**
     * Dispatches the Action
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(Request_Interface $request)
    {
        if ($request->is_dispatched() && $request->get_action_name() !== 'denied' && !$this->_is_allowed()) {
            $this->_response->set_status_header(403, '1.1', 'Forbidden');
            if (!$this->_auth->is_logged_in()) {
                return $this->_redirect('*/auth/login');
            }
            $this->_view->load_layout(['default', 'adminhtml_denied'], true, true, false);
            $this->_view->render_layout();
            $this->_request->set_dispatched(true);
            return $this->_response;
        }
        if ($this->_is_url_checked()) {
            $this->_action_flag->set('', self::FLAG_IS_URLS_CHECKED, true);
        }
        $this->_process_locale_settings();
        // Need to preload isFirstPageAfterLogin (see https://github.com/magento/magento2/issues/15510)
        if ($this->_auth->is_logged_in()) {
            $this->_auth->get_auth_storage()->is_first_page_after_login();
        }
        return parent::dispatch($request);
    }
    /**
     * Check url keys. If non valid - redirect
     *
     * @return bool
     *
     * @see \Magento\Backend\App\Request\BackendValidator for default request validation.
     */
    public function _process_url_keys()
    {
        $_is_valid_form_key = true;
        $_is_valid_secret_key = true;
        $_key_error_msg = '';
        if ($this->_auth->is_logged_in()) {
            if ($this->get_request()->is_post()) {
                $_is_valid_form_key = $this->_form_key_validator->validate($this->get_request());
                $_key_error_msg = __('Invalid Form Key. Please refresh the page.');
            } elseif ($this->_backend_url->use_secret_key()) {
                $_is_valid_secret_key = $this->_validate_secret_key();
                $_key_error_msg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_is_valid_form_key || !$_is_valid_secret_key) {
            $this->_action_flag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_action_flag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->get_request()->get_query('isAjax', false) || $this->get_request()->get_query('ajax', false)) {
                $this->get_response()->represent_json($this->_object_manager->get(\Magento\Framework\Json\Helper\Data::class)->json_encode(['error' => true, 'message' => $_key_error_msg]));
            } else {
                $this->_redirect($this->_backend_url->get_startup_page_url());
            }
            return false;
        }
        return true;
    }
    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function get_url($route = '', $params = [])
    {
        return $this->_helper->get_url($route, $params);
    }
    /**
     * Determines whether current user is allowed to access Action
     *
     * @return bool
     */
    protected function _is_allowed()
    {
        return $this->_authorization->is_allowed(static::ADMIN_RESOURCE);
    }
    /**
     * Retrieve adminhtml session model object
     *
     * @return \Magento\Backend\Model\Session
     */
    protected function _get_session()
    {
        return $this->_session;
    }
    /**
     * Returns instantiated Message\ManagerInterface.
     *
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function get_message_manager()
    {
        return $this->message_manager;
    }
    /**
     * Define active menu item in menu block
     *
     * @param string $itemId current active menu item
     * @return $this
     */
    protected function _set_active_menu($item_id)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menu_block = $this->_view->get_layout()->get_block('menu');
        $menu_block->set_active($item_id);
        $parents = $menu_block->get_menu_model()->get_parent_items($item_id);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            $this->_view->get_page()->get_config()->get_title()->prepend($item->get_title());
        }
        return $this;
    }
    /**
     * Adds element to Breadcrumbs block
     *
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    protected function _add_breadcrumb($label, $title, $link = null)
    {
        $this->_view->get_layout()->get_block('breadcrumbs')->add_link($label, $title, $link);
        return $this;
    }
    /**
     * Adds block to `content` block
     *
     * @return $this
     */
    protected function _add_content(Abstract_Block $block)
    {
        return $this->_move_block_to_container($block, 'content');
    }
    /**
     * Moves Block to `left` container
     *
     * @return $this
     */
    protected function _add_left(Abstract_Block $block)
    {
        return $this->_move_block_to_container($block, 'left');
    }
    /**
     * Adds Block to `js` container
     *
     * @return $this
     */
    protected function _add_js(Abstract_Block $block)
    {
        return $this->_move_block_to_container($block, 'js');
    }
    /**
     * Set specified block as an anonymous child to specified container.
     *
     * @return $this
     */
    private function _move_block_to_container(Abstract_Block $block, string $container_name): static
    {
        $this->_view->get_layout()->set_child($container_name, $block->get_name_in_layout(), '');
        return $this;
    }
    /**
     * Check whether url is checked
     *
     * @return bool
     */
    protected function _is_url_checked()
    {
        return !$this->_action_flag->get('', self::FLAG_IS_URLS_CHECKED) && !$this->get_request()->is_forwarded() && !$this->_get_session()->get_is_url_notice(true) && !$this->_can_use_base_url;
    }
    /**
     * Set session locale, process force locale set through url params
     *
     * @return $this
     */
    protected function _process_locale_settings()
    {
        $force_locale = $this->get_request()->get_param('locale');
        if ($this->_object_manager->get(\Magento\Framework\Validator\Locale::class)->is_valid($force_locale)) {
            $this->_get_session()->set_session_locale($force_locale);
        }
        if ($this->_get_session()->get_locale() === null) {
            $this->_get_session()->set_locale($this->_locale_resolver->get_locale());
        }
        return $this;
    }
    /**
     * Set redirect into response
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param string $path
     * @param array $arguments
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_get_session()->set_is_url_notice($this->_action_flag->get('', self::FLAG_IS_URLS_CHECKED));
        $this->get_response()->set_redirect($this->get_url($path, $arguments));
        return $this->get_response();
    }
    /**
     * Forward to action
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @return void
     */
    protected function _forward($action, $controller = null, $module = null, ?array $params = null)
    {
        $this->_get_session()->set_is_url_notice($this->_action_flag->get('', self::FLAG_IS_URLS_CHECKED));
        parent::_forward($action, $controller, $module, $params);
    }
    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validate_secret_key()
    {
        if (is_array($this->_public_actions) && in_array($this->get_request()->get_action_name(), $this->_public_actions)) {
            return true;
        }
        $secret_key = $this->get_request()->get_param(Url_Interface::SECRET_KEY_PARAM_NAME);
        if (!$secret_key || !Security::compare_strings($secret_key, $this->_backend_url->get_secret_key())) {
            return false;
        }
        return true;
    }
}