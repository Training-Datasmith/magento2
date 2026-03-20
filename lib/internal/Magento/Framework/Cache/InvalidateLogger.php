<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\App\Request\Http as HttpRequest;
use Psr\Log\Logger_Interface as Logger;
/**
 * Invalidate logger cache.
 */
class Invalidate_Logger
{
    /**
     * @var HttpRequest
     */
    private $request;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @param HttpRequest $request
     * @param Logger $logger
     */
    public function __construct(Http_Request $request, Logger $logger)
    {
        $this->request = $request;
        $this->logger = $logger;
    }
    /**
     * Logger invalidate cache
     *
     * @param mixed $invalidateInfo
     * @return void
     */
    public function execute($invalidate_info)
    {
        $context = $this->make_params($invalidate_info);
        if (isset($invalidate_info['tags'], $invalidate_info['mode'])) {
            if ($invalidate_info['mode'] === 'all' && is_array($invalidate_info['tags']) && empty($invalidate_info['tags'])) {
                // If we are sending a purge request to all cache storage capture the trace
                // This is not a usual flow, and likely a bug is causing a performance issue
                $context['trace'] = (string) new \Exception('full purge of cache storage triggered');
            }
        }
        $this->logger->debug('cache_invalidate: ', $context);
    }
    /**
     * Make extra data to logger message
     *
     * @param mixed $invalidateInfo
     * @return array
     */
    private function make_params($invalidate_info)
    {
        $method = $this->request->get_method();
        $url = $this->request->get_uri_string();
        return compact('method', 'url', 'invalidateInfo');
    }
    /**
     * Log critical
     *
     * @param string $message
     * @param mixed $params
     * @return void
     */
    public function critical($message, $params)
    {
        $this->logger->critical($message, $this->make_params($params));
    }
    /**
     * Log warning
     *
     * @param string $message
     * @param mixed $params
     * @return void
     */
    public function warning($message, $params)
    {
        $this->logger->warning($message, $this->make_params($params));
    }
}