<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Arguments;

use Magento\Framework\Data\Argument\Interpreter\Constant;
use Magento\Framework\Data\Argument\Interpreter_Interface;
/**
 * Interpreter that returns value of an application argument, retrieving its name from a constant
 */
class Argument_Interpreter implements Interpreter_Interface
{
    /**
     * @var Constant
     */
    private $const_interpreter;
    /**
     * @param Constant $constInterpreter
     */
    public function __construct(Constant $const_interpreter)
    {
        $this->const_interpreter = $const_interpreter;
    }
    /**
     * {@inheritdoc}
     * @return mixed
     */
    public function evaluate(array $data)
    {
        return ['argument' => $this->const_interpreter->evaluate($data)];
    }
}