<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Reflection\Methods_Map;
use Magento\Framework\Reflection\Type_Processor;
/**
 * Communication configuration validator.
 */
class Validator
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
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     */
    public function __construct(Type_Processor $type_processor, Methods_Map $methods_map)
    {
        $this->type_processor = $type_processor;
        $this->methods_map = $methods_map;
    }
    /**
     * Validate response schema definition for topic
     *
     * @param string $responseSchema
     * @param string $topicName
     * @return void
     */
    public function validate_response_schema_type($response_schema, $topic_name)
    {
        try {
            $this->validate_type($response_schema);
        } catch (\InvalidArgumentException $e) {
            throw new \LogicException('Response schema definition has service class with wrong annotated methods', $e->get_code(), $e);
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Response schema definition for topic "%s" should reference existing type or service class. ' . 'Given "%s"', $topic_name, $response_schema));
        }
    }
    /**
     * Validate request schema definition for topic
     *
     * @param string $requestSchema
     * @param string $topicName
     * @return void
     */
    public function validate_request_schema_type($request_schema, $topic_name)
    {
        try {
            $this->validate_type($request_schema);
        } catch (\InvalidArgumentException $e) {
            throw new \LogicException('Request schema definition has service class with wrong annotated methods', $e->get_code(), $e);
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Request schema definition for topic "%s" should reference existing service class. ' . 'Given "%s"', $topic_name, $request_schema));
        }
    }
    /**
     * Validate service method specified in the definition of handler
     *
     * @param string $serviceName
     * @param string $methodName
     * @param string $handlerName
     * @param string $topicName
     * @return void
     */
    public function validate_response_handlers_type($service_name, $method_name, $handler_name, $topic_name)
    {
        try {
            $this->methods_map->get_method_params($service_name, $method_name);
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Service method specified in the definition of handler "%s" for topic "%s"' . ' is not available. Given "%s"', $handler_name, $topic_name, $service_name . '::' . $method_name));
        }
    }
    /**
     * Ensure that specified type is either a simple type or a valid service data type.
     *
     * @param string $typeName
     * @return $this
     * @throws \Exception In case when type is invalid
     * @throws \InvalidArgumentException if methods don't have annotation
     */
    protected function validate_type($type_name)
    {
        if ($this->type_processor->is_type_simple($type_name)) {
            return $this;
        }
        if ($this->type_processor->is_array_type($type_name)) {
            $array_item_type = $this->type_processor->get_array_item_type($type_name);
            $this->methods_map->get_methods_map($array_item_type);
        } else {
            $this->methods_map->get_methods_map($type_name);
        }
        return $this;
    }
}