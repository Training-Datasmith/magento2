<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config\Reader\Xml_Reader;

use Magento\Framework\Communication\Config\Validator as ConfigValidator;
use Magento\Framework\Reflection\Methods_Map;
use Magento\Framework\Reflection\Type_Processor;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Communication configuration validator.
 */
class Validator extends Config_Validator
{
    /**
     * @var TypeProcessor
     */
    private $type_processor;
    /**
     * @var MethodsMap
     */
    private $methods_map;
    /**
     * @var BooleanUtils
     */
    private $boolean_utils;
    /**
     * Initialize dependencies
     *
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(Type_Processor $type_processor, Methods_Map $methods_map, Boolean_Utils $boolean_utils)
    {
        $this->boolean_utils = $boolean_utils;
        $this->type_processor = $type_processor;
        $this->methods_map = $methods_map;
        parent::__construct($type_processor, $methods_map);
    }
    /**
     * Validate service method
     *
     * @param string $serviceMethod
     * @param string $topicName
     * @param string $className
     * @param string $methodName
     * @return void
     */
    public function validate_service_method($service_method, $topic_name, $class_name, $method_name)
    {
        try {
            $this->methods_map->get_method_params($class_name, $method_name);
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Service method specified in the definition of topic "%s" is not available. Given "%s"', $topic_name, $service_method));
        }
    }
    /**
     * Validate response request
     *
     * @param string $requestResponseSchema
     * @param string $requestSchema
     * @param string $topicName
     * @param string $responseSchema
     * @param array $handlers
     * @return void
     */
    public function validate_response_request($request_response_schema, $request_schema, $topic_name, $response_schema, $handlers)
    {
        /** Validate schema attributes */
        if (!$request_response_schema && !$request_schema) {
            throw new \LogicException(sprintf('Either "request" or "schema" attribute must be specified for topic "%s"', $topic_name));
        }
        if (($request_response_schema || $response_schema) && count($handlers) >= 2) {
            throw new \LogicException(sprintf('Topic "%s" is configured for synchronous requests, that is why it must have exactly one ' . 'response handler declared. The following handlers declared: %s', $topic_name, implode(', ', array_keys($handlers))));
        }
    }
    /**
     * Validate declaration of the topic
     *
     * @param string $requestResponseSchema
     * @param string $topicName
     * @param string $requestSchema
     * @param string $responseSchema
     * @return void
     */
    public function validate_declaration_of_topic($request_response_schema, $topic_name, $request_schema, $response_schema)
    {
        if (!$request_response_schema && !($request_schema && $response_schema) && !$request_schema) {
            throw new \LogicException(sprintf('Declaration of topic "%s" is invalid. Specify at least "request" or "schema".', $topic_name));
        }
    }
}