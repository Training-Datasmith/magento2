<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Interpreter of string data type.
 */
class Base_String_Utils implements Interpreter_Interface
{
    /**
     * @var BooleanUtils
     */
    private $boolean_utils;
    /**
     * BaseStringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(Boolean_Utils $boolean_utils)
    {
        $this->boolean_utils = $boolean_utils;
    }
    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (isset($data['value'])) {
            $result = $data['value'];
            if (!is_string($result)) {
                throw new \InvalidArgumentException('String value is expected.');
            }
        } else {
            $result = '';
        }
        return $result;
    }
}