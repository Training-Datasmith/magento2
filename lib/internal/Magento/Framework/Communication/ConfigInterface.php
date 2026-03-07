<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Communication;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for accessing to communication configuration.
 *
 * @api
 * @since 100.1.0
 */
interface ConfigInterface
{
    public const TOPICS = 'topics';

    public const TOPIC_NAME = 'name';
    public const TOPIC_HANDLERS = 'handlers';
    public const TOPIC_REQUEST = 'request';
    public const TOPIC_RESPONSE = 'response';
    public const TOPIC_IS_SYNCHRONOUS = 'is_synchronous';
    public const TOPIC_REQUEST_TYPE = 'request_type';

    public const TOPIC_REQUEST_TYPE_CLASS = 'object_interface';
    public const TOPIC_REQUEST_TYPE_METHOD = 'service_method_interface';

    public const SCHEMA_METHOD_PARAMS = 'method_params';
    public const SCHEMA_METHOD_RETURN_TYPE = 'method_return_type';
    public const SCHEMA_METHOD_HANDLER = 'method_callback';

    public const SCHEMA_METHOD_PARAM_NAME = 'param_name';
    public const SCHEMA_METHOD_PARAM_POSITION = 'param_position';
    public const SCHEMA_METHOD_PARAM_TYPE = 'param_type';
    public const SCHEMA_METHOD_PARAM_IS_REQUIRED = 'is_required';

    public const HANDLER_TYPE = 'type';
    public const HANDLER_METHOD = 'method';
    public const HANDLER_DISABLED = 'disabled';

    /**
     * Get configuration of the specified topic.
     *
     * @param string $topicName
     * @return array
     * @throws LocalizedException
     * @since 100.1.0
     */
    public function getTopic($topicName);

    /**
     * Get topic handlers.
     *
     * @param string $topicName
     * @return array
     * @since 100.1.0
     */
    public function getTopicHandlers($topicName);

    /**
     * Get list of all declared topics and their configuration.
     *
     * @return array
     * @since 100.1.0
     */
    public function getTopics();
}
