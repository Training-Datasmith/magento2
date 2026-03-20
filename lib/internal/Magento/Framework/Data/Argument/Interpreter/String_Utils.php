<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Interpreter of string data type that may optionally perform text translation.
 */
class String_Utils implements Interpreter_Interface
{
    /**
     * @var BaseStringUtils
     */
    private $base_string_utils;
    /**
     * @var BooleanUtils
     */
    private $boolean_utils;
    /**
     * StringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @param BaseStringUtils $baseStringUtils
     */
    public function __construct(Boolean_Utils $boolean_utils, Base_String_Utils $base_string_utils)
    {
        $this->boolean_utils = $boolean_utils;
        $this->base_string_utils = $base_string_utils;
    }
    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $result = $this->base_string_utils->evaluate($data);
        $need_translation = isset($data['translate']) ? $this->boolean_utils->to_boolean($data['translate']) : false;
        if ($need_translation) {
            $result = (string) new \Magento\Framework\Phrase($result);
        }
        return $result;
    }
}