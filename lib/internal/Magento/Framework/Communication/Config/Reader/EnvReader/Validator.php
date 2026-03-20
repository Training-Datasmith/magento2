<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config\Reader\Env_Reader;

use Magento\Framework\Communication\Config\Validator as ConfigValidator;
use Magento\Framework\Communication\Config_Interface;
use Magento\Framework\Reflection\Methods_Map;
use Magento\Framework\Reflection\Type_Processor;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Communication configuration validator. Validates data, that have been read from env.php.
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
     * Validate config data
     *
     * @param array $configData
     * @return void
     */
    public function validate($config_data)
    {
        if (isset($config_data[Config_Interface::TOPICS])) {
            foreach ($config_data[Config_Interface::TOPICS] as $topic_name_key => $config_data_item) {
                $this->validate_topic_name($config_data_item, $topic_name_key);
                $this->validate_topic($config_data_item, $topic_name_key);
                $topic_name = $config_data_item[Config_Interface::TOPIC_NAME];
                $response_schema = $config_data_item[Config_Interface::TOPIC_RESPONSE];
                $request_schema = $config_data_item[Config_Interface::TOPIC_REQUEST];
                $request_type = $config_data_item[Config_Interface::TOPIC_REQUEST_TYPE];
                $this->validate_topic_response_handler($config_data_item);
                $this->validate_request_type_value($request_type, $topic_name, $request_schema);
                if ($request_type == Config_Interface::TOPIC_REQUEST_TYPE_CLASS) {
                    $this->validate_request_schema_type($request_schema, $topic_name);
                }
                if ($response_schema) {
                    $this->validate_response_schema_type($response_schema, $topic_name);
                }
            }
        }
    }
    /**
     * Validate topic name from config data
     *
     * @param mixed $configDataItem
     * @param string $topicName
     * @return void
     */
    private function validate_topic_name($config_data_item, $topic_name)
    {
        if (!is_string($topic_name)) {
            throw new \LogicException(sprintf('Topic "%s" must contain a name', $topic_name));
        }
        if (isset($config_data_item[Config_Interface::TOPIC_NAME])) {
            if ($config_data_item[Config_Interface::TOPIC_NAME] != $topic_name) {
                throw new \LogicException(sprintf('Topic name "%s" and attribute "name" = "%s" must be equal', $topic_name, $config_data_item[Config_Interface::TOPIC_NAME]));
            }
        }
    }
    /**
     * Validate topic from config data
     *
     * @param mixed $configDataItem
     * @param string $topicName
     * @return void
     */
    private function validate_topic($config_data_item, $topic_name)
    {
        $required_fields = [Config_Interface::TOPIC_NAME, Config_Interface::TOPIC_IS_SYNCHRONOUS, Config_Interface::TOPIC_REQUEST, Config_Interface::TOPIC_REQUEST_TYPE, Config_Interface::TOPIC_RESPONSE, Config_Interface::TOPIC_HANDLERS];
        if (!is_array($config_data_item)) {
            throw new \LogicException(sprintf('Topic "%s" must contain data', $topic_name));
        }
        $config_data_item_keys = array_keys($config_data_item);
        $missed_keys = array_diff($required_fields, $config_data_item_keys);
        if (!empty($missed_keys)) {
            throw new \LogicException(sprintf('Topic "%s" has missed keys: [%s]', $config_data_item[Config_Interface::TOPIC_NAME], implode(', ', $missed_keys)));
        }
        $excessive_keys = array_diff($config_data_item_keys, $required_fields);
        if (!empty($excessive_keys)) {
            throw new \LogicException(sprintf('Topic "%s" has excessive keys: [%s]', $config_data_item[Config_Interface::TOPIC_NAME], implode(', ', $excessive_keys)));
        }
        try {
            $this->boolean_utils->to_boolean($config_data_item[Config_Interface::TOPIC_IS_SYNCHRONOUS]);
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('The attribute "%s" for topic "%s" should have the value of the boolean type. ' . 'Given value is "%s"', Config_Interface::TOPIC_IS_SYNCHRONOUS, $config_data_item[Config_Interface::TOPIC_NAME], var_export($config_data_item[Config_Interface::TOPIC_IS_SYNCHRONOUS], true)));
        }
    }
    /**
     * Validate topic response handler from config data
     *
     * @param array $configDataItem
     * @return void
     */
    private function validate_topic_response_handler($config_data_item)
    {
        $topic_name = $config_data_item[Config_Interface::TOPIC_NAME];
        if (!is_array($config_data_item[Config_Interface::TOPIC_HANDLERS])) {
            throw new \LogicException(sprintf('Handlers in the topic "%s" must be an array', $topic_name));
        }
        if ($this->boolean_utils->to_boolean($config_data_item[Config_Interface::TOPIC_IS_SYNCHRONOUS]) && count($config_data_item[Config_Interface::TOPIC_HANDLERS]) != 1) {
            throw new \LogicException(sprintf('Topic "%s" is configured for synchronous requests, that is why it must have exactly one ' . 'response handler declared. The following handlers declared: %s', $topic_name, implode(', ', array_keys($config_data_item[Config_Interface::TOPIC_HANDLERS]))));
        }
        foreach ($config_data_item[Config_Interface::TOPIC_HANDLERS] as $handler_name => $handler) {
            $service_name = $handler[Config_Interface::HANDLER_TYPE];
            $method_name = $handler[Config_Interface::HANDLER_METHOD];
            if (isset($handler[Config_Interface::HANDLER_DISABLED]) && $this->boolean_utils->to_boolean($handler[Config_Interface::HANDLER_DISABLED])) {
                throw new \LogicException(sprintf('Disabled handler "%s" for topic "%s" cannot be added to the config file', $handler_name, $topic_name));
            }
            $this->validate_response_handlers_type($service_name, $method_name, $handler_name, $topic_name);
        }
    }
    /**
     * @param string $requestType
     * @param string $topicName
     * @param string $requestSchema
     * @return void
     */
    protected function validate_request_type_value($request_type, $topic_name, $request_schema)
    {
        if (!in_array($request_type, [Config_Interface::TOPIC_REQUEST_TYPE_CLASS, Config_Interface::TOPIC_REQUEST_TYPE_METHOD])) {
            throw new \LogicException(sprintf('Request schema type for topic "%s" must be "%s" or "%s". Given "%s"', $topic_name, Config_Interface::TOPIC_REQUEST_TYPE_CLASS, Config_Interface::TOPIC_REQUEST_TYPE_METHOD, $request_schema));
        }
    }
}