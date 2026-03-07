<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Model\System\Message;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Throwable;

/**
 * @api
 * @since 100.0.2
 */
class Security implements MessageInterface
{
    /**
     * Cache key for saving verification result
     */
    public const VERIFICATION_RESULT_CACHE_KEY = 'configuration_files_access_level_verification';

    /**
     * File path for verification
     */
    private string $_filePath = 'app/etc/config.php';

    /**
     * Time out for HTTP verification request
     */
    private int $_verificationTimeOut = 2;

    /**
     * @var CurlFactory
     */
    protected $_curlFactory;

    public function __construct(
        protected \Magento\Framework\App\CacheInterface $_cache,
        protected \Magento\Backend\App\ConfigInterface $_backendConfig,
        protected \Magento\Framework\App\Config\ScopeConfigInterface $_config,
        CurlFactory $curlFactory
    ) {
        $this->_curlFactory = $curlFactory;
    }

    /**
     * Check verification result and return true if system must to show notification message
     */
    private function _canShowNotification(): bool
    {
        if ($this->_cache->load(self::VERIFICATION_RESULT_CACHE_KEY)) {
            return false;
        }

        if ($this->_isFileAccessible()) {
            return true;
        }

        $adminSessionLifetime = (int)$this->_backendConfig->getValue('admin/security/session_lifetime');
        $this->_cache->save(true, self::VERIFICATION_RESULT_CACHE_KEY, [], $adminSessionLifetime);
        return false;
    }

    /**
     * If file is accessible return true or false
     */
    private function _isFileAccessible(): bool
    {
        $unsecureBaseURL = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        /** @var $http Curl */
        $http = $this->_curlFactory->create();
        $http->setOptions(['timeout' => $this->_verificationTimeOut]);
        $http->write(Request::METHOD_POST, $unsecureBaseURL . $this->_filePath);
        $responseBody = $http->read();
        $responseCode = $this->extractCodeFromResponse($responseBody);
        $http->close();

        return $responseCode == 200;
    }

    /**
     * Retrieve unique message identity
     */
    public function getIdentity(): string
    {
        return 'security';
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->_canShowNotification();
    }

    /**
     * Retrieve message text
     *
     * @return Phrase
     */
    public function getText()
    {
        return __(
            'Your web server is set up incorrectly and allows unauthorized access to sensitive files. '
            . 'Please contact your hosting provider.'
        );
    }

    /**
     * Retrieve message severity
     */
    public function getSeverity(): int
    {
        return MessageInterface::SEVERITY_CRITICAL;
    }

    /**
     * Extract the response code from a response string
     *
     *
     * @return false|int
     */
    private function extractCodeFromResponse(string $responseString)
    {
        try {
            $responseCode = Response::fromString($responseString)->getStatusCode();
        } catch (Throwable) {
            $responseCode = false;
        }

        return $responseCode;
    }
}
