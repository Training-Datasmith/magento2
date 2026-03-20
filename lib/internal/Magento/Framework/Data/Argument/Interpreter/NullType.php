<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter_Interface;
/**
 * Interpreter of NULL data type
 */
class Null_Type implements Interpreter_Interface
{
    /**
     * {@inheritdoc}
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function evaluate(array $data)
    {
        return null;
    }
}