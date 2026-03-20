<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Parser helper for communication-related configs.
 */
class Config_Parser
{
    public const TYPE_NAME = 'typeName';
    public const METHOD_NAME = 'methodName';
    /**
     * Parse service method name.
     *
     * @param string $serviceMethod
     * @return array Contains class name and method name
     * @throws LocalizedException
     */
    public function parse_service_method($service_method)
    {
        $pattern = '/^([a-zA-Z]+[a-zA-Z0-9\\\\]+)::([a-zA-Z0-9]+)$/';
        preg_match($pattern, $service_method, $matches);
        if (!isset($matches[1]) || !isset($matches[2])) {
            throw new Localized_Exception(new Phrase('The "%serviceMethod" service method must match the "%pattern" pattern.', ['serviceMethod' => $service_method, 'pattern' => $pattern]));
        }
        $class_name = $matches[1];
        $method_name = $matches[2];
        return [self::TYPE_NAME => $class_name, self::METHOD_NAME => $method_name];
    }
}