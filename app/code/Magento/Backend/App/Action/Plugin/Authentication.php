<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\Framework\Exception\Authentication_Exception;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authentication
{
    /**
     * @var string[]
     */
    protected $_open_actions = ['forgotpassword', 'resetpassword', 'resetpasswordpost', 'logout', 'refresh'];
    public function __construct(protected \Magento\Backend\Model\Auth $_auth, protected \Magento\Backend\Model\Url_Interface $_url, protected \Magento\Framework\App\Response_Interface $_response, protected \Magento\Framework\App\Action_Flag $_action_flag, protected \Magento\Framework\Message\Manager_Interface $message_manager, protected \Magento\Backend\Model\Url_Interface $backend_url, protected \Magento\Framework\Controller\Result\Redirect_Factory $result_redirect_factory, protected \Magento\Backend\App\Backend_App_List $backend_app_list, protected \Magento\Framework\Data\Form\Form_Key\Validator $form_key_validator)
    {
    }
    /**
     * Ensures user is authenticated before accessing backend action controllers.
     *
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function around_dispatch(\Magento\Backend\App\Abstract_Action $subject, \Closure $proceed, \Magento\Framework\App\Request_Interface $request)
    {
        $requested_action_name = $request->get_action_name();
        if (in_array($requested_action_name, $this->_open_actions)) {
            $request->set_dispatched(true);
        } else {
            if ($this->_auth->get_user()) {
                $this->_auth->get_user()->reload();
            }
            if (!$this->_auth->is_logged_in()) {
                $this->_process_not_logged_in_user($request);
            } else {
                $this->_auth->get_auth_storage()->prolong();
                $backend_app = null;
                if ($request->get_param('app')) {
                    $backend_app = $this->backend_app_list->get_current_app();
                }
                if ($backend_app) {
                    $result_redirect = $this->result_redirect_factory->create();
                    $base_url = \Magento\Framework\App\Request\Http::get_url_no_script($this->backend_url->get_base_url());
                    $base_url = $base_url . $backend_app->get_startup_page();
                    return $result_redirect->set_url($base_url);
                }
            }
        }
        $this->_auth->get_auth_storage()->refresh_acl();
        return $proceed($request);
    }
    /**
     * Process not logged in user data
     *
     * @return void
     */
    protected function _process_not_logged_in_user(\Magento\Framework\App\Request_Interface $request)
    {
        $is_redirect_needed = false;
        if ($request->get_post('login')) {
            if ($this->form_key_validator->validate($request)) {
                if ($this->_perform_login($request)) {
                    $is_redirect_needed = $this->_redirect_if_needed_after_login($request);
                }
            } else {
                $this->_action_flag->set('', \Magento\Framework\App\Action_Interface::FLAG_NO_DISPATCH, true);
                $this->_response->set_redirect($this->_url->get_current_url());
                $this->message_manager->add_error_message(__('Invalid Form Key. Please refresh the page.'));
                $is_redirect_needed = true;
            }
        }
        if (!$is_redirect_needed && !$request->is_forwarded()) {
            if ($request->get_param('isIframe')) {
                $request->set_forwarded(true)->set_route_name('adminhtml')->set_controller_name('auth')->set_action_name('deniedIframe')->set_dispatched(false);
            } elseif ($request->get_param('isAjax')) {
                $request->set_forwarded(true)->set_route_name('adminhtml')->set_controller_name('auth')->set_action_name('deniedJson')->set_dispatched(false);
            } else {
                $request->set_forwarded(true)->set_route_name('adminhtml')->set_controller_name('auth')->set_action_name('login')->set_dispatched(false);
            }
        }
    }
    /**
     * Performs login, if user submitted login form
     *
     * @return bool
     */
    protected function _perform_login(\Magento\Framework\App\Request_Interface $request)
    {
        $output_value = true;
        $post_login = $request->get_post('login');
        $username = $post_login['username'] ?? '';
        $password = $post_login['password'] ?? '';
        $request->set_post_value('login', null);
        try {
            $this->_auth->login($username, $password);
        } catch (Authentication_Exception $e) {
            if (!$request->get_param('messageSent')) {
                $this->message_manager->add_error_message($e->get_message());
                $request->set_param('messageSent', true);
                $output_value = false;
            }
        }
        return $output_value;
    }
    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     */
    protected function _redirect_if_needed_after_login(\Magento\Framework\App\Request_Interface $request): bool
    {
        $request_uri = null;
        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_url->use_secret_key()) {
            // The requested URL has an invalid secret key and therefore redirecting to this URL
            // will cause a security vulnerability.
            $request_uri = $this->_url->get_url($this->_url->get_startup_page_url());
        } elseif ($request) {
            $request_uri = $request->get_request_uri();
        }
        if (!$request_uri) {
            return false;
        }
        $this->_response->set_redirect($request_uri);
        $this->_action_flag->set('', \Magento\Framework\App\Action_Interface::FLAG_NO_DISPATCH, true);
        return true;
    }
}