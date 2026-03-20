<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Framework\Communication\Config_Interface as CommunicationConfig;
/**
 * Class for accessing to Webapi_Async configuration.
 *
 * @api
 * @since 100.2.3
 */
interface Config_Interface
{
    /**#@+
     * Constants for Webapi Asynchronous Config generation
     */
    public const CACHE_ID = 'webapi_async_config';
    public const TOPIC_PREFIX = 'async.';
    public const DEFAULT_CONSUMER_INSTANCE = Mass_Consumer::class;
    public const DEFAULT_CONSUMER_CONNECTION = 'amqp';
    public const DEFAULT_CONSUMER_MAX_MESSAGE = null;
    public const SERVICE_PARAM_KEY_INTERFACE = 'interface';
    public const SERVICE_PARAM_KEY_METHOD = 'method';
    public const SERVICE_PARAM_KEY_TOPIC = 'topic';
    public const DEFAULT_HANDLER_NAME = 'async';
    public const SYSTEM_TOPIC_NAME = 'async.system.required.wrapper.topic';
    public const SYSTEM_TOPIC_CONFIGURATION = [Communication_Config::TOPIC_NAME => self::SYSTEM_TOPIC_NAME, Communication_Config::TOPIC_IS_SYNCHRONOUS => false, Communication_Config::TOPIC_REQUEST => Operation_Interface::class, Communication_Config::TOPIC_REQUEST_TYPE => Communication_Config::TOPIC_REQUEST_TYPE_CLASS, Communication_Config::TOPIC_RESPONSE => null, Communication_Config::TOPIC_HANDLERS => []];
    /**#@-*/
    /**
     * Get array of generated topics name and related to this topic service class and methods
     *
     * @return array
     * @since 100.2.3
     */
    public function get_services();
    /**
     * Get topic name from webapi_async_config services config array by route url and http method
     *
     * @param string $routeUrl
     * @param string $httpMethod GET|POST|PUT|DELETE
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.3
     */
    public function get_topic_name($route_url, $http_method);
}