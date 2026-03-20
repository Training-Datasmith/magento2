<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Stdlib\Boolean_Utils;
class Data_Object implements Interpreter_Interface
{
    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     */
    protected $boolean_utils;
    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(Boolean_Utils $boolean_utils)
    {
        $this->boolean_utils = $boolean_utils;
    }
    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function evaluate(array $data): array
    {
        $result = ['instance' => $data['value']];
        if (array_key_exists('sortOrder', $data)) {
            $result['sortOrder'] = $data['sortOrder'];
        }
        if (isset($data['shared'])) {
            $result['shared'] = $this->boolean_utils->to_boolean($data['shared']);
        }
        return $result;
    }
}