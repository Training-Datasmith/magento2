<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config\Reader\Xml_Reader;

use Magento\Framework\Communication\Config\Config_Parser;
use Magento\Framework\Communication\Config\Reflection_Generator;
use Magento\Framework\Communication\Config_Interface as Config;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Converts Communication config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * @deprecated
     * @see ConfigParser::parseServiceMethod
     */
    public const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';
    /**
     * @var ReflectionGenerator
     */
    private $reflection_generator;
    /**
     * @var BooleanUtils
     */
    private $boolean_utils;
    /**
     * @var Validator
     */
    private $xml_validator;
    /**
     * @var ConfigParser
     */
    private $config_parser;
    /**
     * Initialize dependencies
     *
     * @param ReflectionGenerator $reflectionGenerator
     * @param BooleanUtils $booleanUtils
     * @param Validator $xmlValidator
     */
    public function __construct(Reflection_Generator $reflection_generator, Boolean_Utils $boolean_utils, Validator $xml_validator)
    {
        $this->reflection_generator = $reflection_generator;
        $this->boolean_utils = $boolean_utils;
        $this->xml_validator = $xml_validator;
    }
    /**
     * The getter function to get the new ConfigParser dependency.
     *
     * @return \Magento\Framework\Communication\Config\ConfigParser
     * @deprecated 101.0.0
     */
    private function get_config_parser()
    {
        if ($this->config_parser === null) {
            $this->config_parser = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Communication\Config\Config_Parser::class);
        }
        return $this->config_parser;
    }
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $topics = $this->extract_topics($source);
        return [Config::TOPICS => $topics];
    }
    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extract_topics($config)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->get_elements_by_tag_name('topic') as $topic_node) {
            $topic_attributes = $topic_node->attributes;
            $topic_name = $topic_attributes->get_named_item('name')->node_value;
            $service_method = $this->get_service_method_by_schema($topic_node);
            $request_response_schema = $service_method ? $this->reflection_generator->extract_method_metadata($service_method[Config_Parser::TYPE_NAME], $service_method[Config_Parser::METHOD_NAME]) : null;
            $request_schema = $this->extract_topic_request_schema($topic_node);
            $response_schema = $this->extract_topic_response_schema($topic_node);
            $handlers = $this->extract_topic_response_handlers($topic_node);
            $this->xml_validator->validate_response_request($request_response_schema, $request_schema, $topic_name, $response_schema, $handlers);
            $this->xml_validator->validate_declaration_of_topic($request_response_schema, $topic_name, $request_schema, $response_schema);
            $is_synchronous = $this->extract_topic_is_synchronous($topic_node);
            if ($service_method) {
                $output[$topic_name] = $this->reflection_generator->generate_topic_config_for_service_method($topic_name, $service_method[Config_Parser::TYPE_NAME], $service_method[Config_Parser::METHOD_NAME], $handlers, $is_synchronous);
            } elseif ($request_schema && $response_schema) {
                $output[$topic_name] = [Config::TOPIC_NAME => $topic_name, Config::TOPIC_IS_SYNCHRONOUS => $is_synchronous, Config::TOPIC_REQUEST => $request_schema, Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_CLASS, Config::TOPIC_RESPONSE => $is_synchronous ? $response_schema : null, Config::TOPIC_HANDLERS => $handlers];
            } elseif ($request_schema) {
                $output[$topic_name] = [Config::TOPIC_NAME => $topic_name, Config::TOPIC_IS_SYNCHRONOUS => false, Config::TOPIC_REQUEST => $request_schema, Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_CLASS, Config::TOPIC_RESPONSE => null, Config::TOPIC_HANDLERS => $handlers];
            }
        }
        return $output;
    }
    /**
     * Extract response handlers.
     *
     * @param \DOMNode $topicNode
     * @return array List of handlers, each contain service name and method name
     */
    protected function extract_topic_response_handlers($topic_node)
    {
        $topic_name = $topic_node->attributes->get_named_item('name')->node_value;
        $topic_child_nodes = $topic_node->child_nodes;
        $handler_nodes = [];
        /** @var \DOMNode $topicChildNode */
        foreach ($topic_child_nodes as $topic_child_node) {
            if ($topic_child_node->node_name === 'handler') {
                $handler_attributes = $topic_child_node->attributes;
                if ($handler_attributes->get_named_item('disabled') && $this->boolean_utils->to_boolean($handler_attributes->get_named_item('disabled')->node_value)) {
                    continue;
                }
                $handler_name = $handler_attributes->get_named_item('name')->node_value;
                $service_type = $handler_attributes->get_named_item('type')->node_value;
                $method_name = $handler_attributes->get_named_item('method')->node_value;
                $this->xml_validator->validate_response_handlers_type($service_type, $method_name, $handler_name, $topic_name);
                $handler_nodes[$handler_name] = [Config::HANDLER_TYPE => $service_type, Config::HANDLER_METHOD => $method_name];
            }
        }
        return $handler_nodes;
    }
    /**
     * Extract request schema class name.
     *
     * @param \DOMNode $topicNode
     * @return string|null
     */
    protected function extract_topic_request_schema($topic_node)
    {
        $topic_attributes = $topic_node->attributes;
        if (!$topic_attributes->get_named_item('request')) {
            return null;
        }
        $topic_name = $topic_attributes->get_named_item('name')->node_value;
        $request_schema = $topic_attributes->get_named_item('request')->node_value;
        $this->xml_validator->validate_request_schema_type($request_schema, $topic_name);
        return $request_schema;
    }
    /**
     * Extract response schema class name.
     *
     * @param \DOMNode $topicNode
     * @return string|null
     */
    protected function extract_topic_response_schema($topic_node)
    {
        $topic_attributes = $topic_node->attributes;
        if (!$topic_attributes->get_named_item('response')) {
            return null;
        }
        $topic_name = $topic_attributes->get_named_item('name')->node_value;
        $response_schema = $topic_attributes->get_named_item('response')->node_value;
        $this->xml_validator->validate_response_schema_type($response_schema, $topic_name);
        return $response_schema;
    }
    /**
     * Get service class and method specified in schema attribute.
     *
     * @param \DOMNode $topicNode
     * @return array|null Contains class name and method name
     */
    protected function get_service_method_by_schema($topic_node)
    {
        $topic_attributes = $topic_node->attributes;
        if (!$topic_attributes->get_named_item('schema')) {
            return null;
        }
        $topic_name = $topic_attributes->get_named_item('name')->node_value;
        $service_method = $topic_attributes->get_named_item('schema')->node_value;
        return $this->parse_service_method($service_method, $topic_name);
    }
    /**
     * Parse service method name, also ensure that it exists.
     *
     * @param string $serviceMethod
     * @param string $topicName
     * @return array Contains class name and method name
     */
    protected function parse_service_method($service_method, $topic_name)
    {
        $parsed_service_method = $this->get_config_parser()->parse_service_method($service_method);
        $this->xml_validator->validate_service_method($service_method, $topic_name, $parsed_service_method[Config_Parser::TYPE_NAME], $parsed_service_method[Config_Parser::METHOD_NAME]);
        return $parsed_service_method;
    }
    /**
     * Extract is_synchronous topic value.
     *
     * @param \DOMNode $topicNode
     * @return bool
     */
    private function extract_topic_is_synchronous($topic_node): bool
    {
        $attribute_name = Config::TOPIC_IS_SYNCHRONOUS;
        $topic_attributes = $topic_node->attributes;
        if (!$topic_attributes->get_named_item($attribute_name)) {
            return true;
        }
        return $this->boolean_utils->to_boolean($topic_attributes->get_named_item($attribute_name)->node_value);
    }
}