<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Widget\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Parser
{
    /**
     * List of allowed operations
     *
     * @var string[]
     */
    protected $_operations = ['-', '+', '/', '*'];
    /**
     * Parse expression
     *
     * @param string $expression
     * @return array
     */
    public function parse_expression($expression)
    {
        $stack = [];
        $expression = $expression ? trim($expression) : '';
        foreach ($this->_operations as $operation) {
            $splitted_expr = preg_split('/\\' . $operation . '/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);
            $count = count($splitted_expr);
            if ($count > 1) {
                for ($i = 0; $i < $count; $i++) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $stack = array_merge($stack, $this->parse_expression($splitted_expr[$i]));
                    if ($i > 0) {
                        $stack[] = $operation;
                    }
                }
                break;
            }
        }
        return empty($stack) ? [$expression] : $stack;
    }
    /**
     * Check if string is operation
     *
     * @param string $operation
     * @return bool
     */
    public function is_operation($operation)
    {
        return in_array($operation, $this->_operations);
    }
}