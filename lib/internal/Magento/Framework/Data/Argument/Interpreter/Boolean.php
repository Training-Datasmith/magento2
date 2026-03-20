<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Interpreter of boolean data type, such as boolean itself or boolean string
 */
class Boolean implements Interpreter_Interface
{
    /**
     * @var BooleanUtils
     */
    private $boolean_utils;
    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(Boolean_Utils $boolean_utils)
    {
        $this->boolean_utils = $boolean_utils;
    }
    /**
     * {@inheritdoc}
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Boolean value is missing.');
        }
        $value = $data['value'];
        return $this->boolean_utils->to_boolean($value);
    }
}