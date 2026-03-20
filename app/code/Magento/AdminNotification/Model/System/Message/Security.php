<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\Curl_Factory;
use Magento\Framework\Notification\Message_Interface;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Throwable;
/**
 * @api
 * @since 100.0.2
 */
class Security implements Message_Interface
{
    /**
     * Cache key for saving verification result
     */
    public const VERIFICATION_RESULT_CACHE_KEY = 'configuration_files_access_level_verification';
    /**
     * File path for verification
     */
    private string $_file_path = 'app/etc/config.php';
    /**
     * Time out for HTTP verification request
     */
    private int $_verification_time_out = 2;
    /**
     * @var CurlFactory
     */
    protected $_curl_factory;
    public function __construct(protected \Magento\Framework\App\Cache_Interface $_cache, protected \Magento\Backend\App\Config_Interface $_backend_config, protected \Magento\Framework\App\Config\Scope_Config_Interface $_config, Curl_Factory $curl_factory)
    {
        $this->_curl_factory = $curl_factory;
    }
    /**
     * Check verification result and return true if system must to show notification message
     */
    private function _can_show_notification(): bool
    {
        if ($this->_cache->load(self::VERIFICATION_RESULT_CACHE_KEY)) {
            return false;
        }
        if ($this->_is_file_accessible()) {
            return true;
        }
        $admin_session_lifetime = (int) $this->_backend_config->get_value('admin/security/session_lifetime');
        $this->_cache->save(true, self::VERIFICATION_RESULT_CACHE_KEY, [], $admin_session_lifetime);
        return false;
    }
    /**
     * If file is accessible return true or false
     */
    private function _is_file_accessible(): bool
    {
        $unsecure_base_url = $this->_config->get_value(Store::XML_PATH_UNSECURE_BASE_URL, 'default');
        /** @var $http Curl */
        $http = $this->_curl_factory->create();
        $http->set_options(['timeout' => $this->_verification_time_out]);
        $http->write(Request::METHOD_POST, $unsecure_base_url . $this->_file_path);
        $response_body = $http->read();
        $response_code = $this->extract_code_from_response($response_body);
        $http->close();
        return $response_code == 200;
    }
    /**
     * Retrieve unique message identity
     */
    public function get_identity(): string
    {
        return 'security';
    }
    /**
     * Check whether
     *
     * @return bool
     */
    public function is_displayed()
    {
        return $this->_can_show_notification();
    }
    /**
     * Retrieve message text
     *
     * @return Phrase
     */
    public function get_text()
    {
        return __('Your web server is set up incorrectly and allows unauthorized access to sensitive files. ' . 'Please contact your hosting provider.');
    }
    /**
     * Retrieve message severity
     */
    public function get_severity(): int
    {
        return Message_Interface::SEVERITY_CRITICAL;
    }
    /**
     * Extract the response code from a response string
     *
     *
     * @return false|int
     */
    private function extract_code_from_response(string $response_string)
    {
        try {
            $response_code = Response::from_string($response_string)->get_status_code();
        } catch (Throwable) {
            $response_code = false;
        }
        return $response_code;
    }
}