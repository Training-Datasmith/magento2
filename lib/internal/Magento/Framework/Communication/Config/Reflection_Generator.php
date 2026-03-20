<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Communication\Config_Interface as Config;
use Magento\Framework\Reflection\Methods_Map;
/**
 * Communication config generator based on service methods reflection
 */
class Reflection_Generator
{
    public const DEFAULT_HANDLER = 'defaultHandler';
    /**
     * @var MethodsMap
     */
    private $methods_map;
    /**
     * Initialize dependencies
     *
     * @param MethodsMap $methodsMap
     */
    public function __construct(Methods_Map $methods_map)
    {
        $this->methods_map = $methods_map;
    }
    /**
     * Extract service method metadata.
     *
     * @param string $className
     * @param string $methodName
     * @return array
     */
    public function extract_method_metadata($class_name, $method_name)
    {
        $result = [Config::SCHEMA_METHOD_PARAMS => [], Config::SCHEMA_METHOD_RETURN_TYPE => $this->methods_map->get_method_return_type($class_name, $method_name), Config::SCHEMA_METHOD_HANDLER => [Config::HANDLER_TYPE => $class_name, Config::HANDLER_METHOD => $method_name]];
        $params_meta = $this->methods_map->get_method_params($class_name, $method_name);
        foreach ($params_meta as $param_position => $param_meta) {
            $result[Config::SCHEMA_METHOD_PARAMS][] = [Config::SCHEMA_METHOD_PARAM_NAME => $param_meta[Methods_Map::METHOD_META_NAME], Config::SCHEMA_METHOD_PARAM_POSITION => $param_position, Config::SCHEMA_METHOD_PARAM_IS_REQUIRED => !$param_meta[Methods_Map::METHOD_META_HAS_DEFAULT_VALUE], Config::SCHEMA_METHOD_PARAM_TYPE => $param_meta[Methods_Map::METHOD_META_TYPE]];
        }
        return $result;
    }
    /**
     * Generate config data based on service method signature.
     *
     * @param string $topicName
     * @param string $serviceType
     * @param string $serviceMethod
     * @param array|null $handlers
     * @param bool|null $isSynchronous
     * @return array
     */
    public function generate_topic_config_for_service_method($topic_name, $service_type, $service_method, $handlers = [], $is_synchronous = null)
    {
        $method_metadata = $this->extract_method_metadata($service_type, $service_method);
        $return_type = $method_metadata[Config::SCHEMA_METHOD_RETURN_TYPE];
        $return_type = $return_type != 'void' && $return_type != 'null' ? $return_type : null;
        if (!isset($is_synchronous)) {
            $is_synchronous = $return_type ? true : false;
        } else {
            $return_type = $is_synchronous ? $return_type : null;
        }
        return [Config::TOPIC_NAME => $topic_name, Config::TOPIC_IS_SYNCHRONOUS => $is_synchronous, Config::TOPIC_REQUEST => $method_metadata[Config::SCHEMA_METHOD_PARAMS], Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_METHOD, Config::TOPIC_RESPONSE => $return_type, Config::TOPIC_HANDLERS => $handlers ?: [self::DEFAULT_HANDLER => $method_metadata[Config::SCHEMA_METHOD_HANDLER]]];
    }
    /**
     * Generate topic name based on service type and method name.
     *
     * Perform the following conversion:
     * \Magento\Customer\Api\RepositoryInterface + getById =>
     * magento.customer.api.repositoryInterface.getById
     *
     * @param string $typeName
     * @param string $methodName
     * @return string
     */
    public function generate_topic_name($type_name, $method_name)
    {
        $parts = explode('\\', ltrim($type_name, '\\'));
        foreach ($parts as &$part) {
            $part = lcfirst($part);
        }
        return implode('.', $parts) . '.' . $method_name;
    }
}