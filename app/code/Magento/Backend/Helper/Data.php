<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Helper;

use Magento\Framework\App\Helper\Abstract_Helper;
/**
 * @api
 * @deprecated 100.2.0
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Data extends Abstract_Helper
{
    public const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';
    /**
     * @var string
     */
    protected $_page_help_url;
    /**
     * @var \Magento\Framework\App\Route\Config
     */
    protected $_route_config;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backend_url;
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;
    /**
     * @var \Magento\Backend\App\Area\FrontNameResolver
     */
    protected $_front_name_resolver;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $math_random;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Framework\App\Route\Config $route_config, \Magento\Framework\Locale\Resolver_Interface $locale, \Magento\Backend\Model\Url_Interface $backend_url, \Magento\Backend\Model\Auth $auth, \Magento\Backend\App\Area\Front_Name_Resolver $front_name_resolver, \Magento\Framework\Math\Random $math_random)
    {
        parent::__construct($context);
        $this->_route_config = $route_config;
        $this->_locale = $locale;
        $this->_backend_url = $backend_url;
        $this->_auth = $auth;
        $this->_front_name_resolver = $front_name_resolver;
        $this->math_random = $math_random;
    }
    /**
     * @return string
     */
    public function get_page_help_url()
    {
        if (!$this->_page_help_url) {
            $this->set_page_help_url();
        }
        return $this->_page_help_url;
    }
    /**
     * @param string|null $url
     * @return $this
     */
    public function set_page_help_url($url = null)
    {
        if ($url === null) {
            $request = $this->_request;
            $front_module = $request->get_controller_module();
            if (!$front_module) {
                $front_module = $this->_route_config->get_modules_by_front_name($request->get_module_name());
                if (empty($front_module) === false) {
                    $front_module = $front_module[0];
                } else {
                    $front_module = null;
                }
            }
            $url = 'http://www.magentocommerce.com/gethelp/';
            $url .= $this->_locale->get_locale() . '/';
            $url .= $front_module . '/';
            $url .= $request->get_controller_name() . '/';
            $url .= $request->get_action_name() . '/';
            $this->_page_help_url = $url;
        }
        $this->_page_help_url = $url;
        return $this;
    }
    /**
     * @param string $suffix
     * @return $this
     */
    public function add_page_help_url($suffix)
    {
        $this->_page_help_url = $this->get_page_help_url() . $suffix;
        return $this;
    }
    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function get_url($route = '', $params = [])
    {
        return $this->_backend_url->get_url($route, $params);
    }
    /**
     * @return int|bool
     */
    public function get_current_user_id()
    {
        if ($this->_auth->get_user()) {
            return $this->_auth->get_user()->get_id();
        }
        return false;
    }
    /**
     * Decode filter string
     *
     * @param string $filterString
     * @return array
     */
    public function prepare_filter_string($filter_string)
    {
        $data = [];
        $filter_string = base64_decode($filter_string);
        parse_str($filter_string, $data);
        array_walk_recursive(
            $data,
            // @codingStandardsIgnoreStart
            /**
             * Decodes URL-encoded string and trims whitespaces from the beginning and end of a string
             *
             * @param string $value
             */
            // @codingStandardsIgnoreEnd
            function (&$value) {
                $value = trim(rawurldecode($value));
            }
        );
        return $data;
    }
    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generate_reset_password_link_token()
    {
        return $this->math_random->get_unique_hash();
    }
    /**
     * Get backend start page URL
     *
     * @return string
     */
    public function get_home_page_url()
    {
        return $this->_backend_url->get_route_url('adminhtml');
    }
    /**
     * Return Backend area front name
     *
     * @param bool $checkHost
     * @return bool|string
     */
    public function get_area_front_name($check_host = false)
    {
        return $this->_front_name_resolver->get_front_name($check_host);
    }
}