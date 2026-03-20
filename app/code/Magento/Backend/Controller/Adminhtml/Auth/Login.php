<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Backend\App\Area\Front_Name_Resolver;
use Magento\Backend\App\Backend_App_List;
use Magento\Backend\Model\Url_Factory;
use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGet;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPost;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Request\Http;
/**
 * @api
 * @since 100.0.2
 */
class Login extends \Magento\Backend\Controller\Adminhtml\Auth implements Http_Get, Http_Post
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;
    /**
     * @var FrontNameResolver
     */
    private $front_name_resolver;
    /**
     * @var BackendAppList
     */
    private $backend_app_list;
    /**
     * @var UrlFactory
     */
    private $backend_url_factory;
    /**
     * @var Http
     */
    private $http;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param FrontNameResolver|null $frontNameResolver
     * @param BackendAppList|null $backendAppList
     * @param UrlFactory|null $backendUrlFactory
     * @param Http|null $http
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\Page_Factory $result_page_factory, ?Front_Name_Resolver $front_name_resolver = null, ?Backend_App_List $backend_app_list = null, ?Url_Factory $backend_url_factory = null, ?Http $http = null)
    {
        $this->result_page_factory = $result_page_factory;
        parent::__construct($context);
        $this->front_name_resolver = $front_name_resolver ?? Object_Manager::get_instance()->get(Front_Name_Resolver::class);
        $this->backend_app_list = $backend_app_list ?? Object_Manager::get_instance()->get(Backend_App_List::class);
        $this->backend_url_factory = $backend_url_factory ?? Object_Manager::get_instance()->get(Url_Factory::class);
        $this->http = $http ?? Object_Manager::get_instance()->get(Http::class);
    }
    /**
     * Administrator login action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->_auth->is_logged_in()) {
            if ($this->_auth->get_auth_storage()->is_first_page_after_login()) {
                $this->_auth->get_auth_storage()->set_is_first_page_after_login(true);
            }
            return $this->get_redirect($this->_backend_url->get_startup_page_url());
        }
        $request_url = $this->get_request()->get_uri();
        if (!$request_url->is_valid() || !$this->is_valid_backend_uri()) {
            return $this->get_redirect($this->get_url('*'));
        }
        return $this->result_page_factory->create();
    }
    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function get_redirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        $result_redirect->set_path($path);
        return $result_redirect;
    }
    /**
     * Verify if correct backend uri requested.
     *
     * @return bool
     */
    private function is_valid_backend_uri(): bool
    {
        $request_uri = $this->get_request()->get_request_uri();
        $backend_app = $this->backend_app_list->get_current_app();
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $base_url = parse_url($this->backend_url_factory->create()->get_base_url(), PHP_URL_PATH);
        if (!$backend_app) {
            $backend_front_name = $this->front_name_resolver->get_front_name();
        } else {
            //In case of application authenticating through the admin login, the script name should be removed
            //from the path, because application has own script.
            $base_url = $this->http->get_url_no_script($base_url);
            $backend_front_name = $backend_app->get_cookie_path();
        }
        return strpos($request_uri, $base_url . $backend_front_name) === 0;
    }
}