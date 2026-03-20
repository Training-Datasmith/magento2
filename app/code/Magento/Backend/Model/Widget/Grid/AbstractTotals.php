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
abstract class Abstract_Totals implements \Magento\Backend\Model\Widget\Grid\Totals_Interface
{
    /**
     * List of columns should be proceed with expression
     * 'key' => column index
     * 'value' => column expression
     *
     * @var array
     */
    protected $_columns = [];
    /**
     * Array of totals based on columns index
     * 'key' => column index
     * 'value' => counted total
     *
     * @var array
     */
    protected $_totals = [];
    /**
     * Factory model
     *
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $_factory;
    /**
     * Parser for expressions like operand operation operand
     *
     * @var \Magento\Backend\Model\Widget\Grid\Parser
     */
    protected $_parser;
    /**
     * @param \Magento\Framework\DataObject\Factory $factory
     * @param \Magento\Backend\Model\Widget\Grid\Parser $parser
     */
    public function __construct(\Magento\Framework\Data_Object\Factory $factory, \Magento\Backend\Model\Widget\Grid\Parser $parser)
    {
        $this->_factory = $factory;
        $this->_parser = $parser;
    }
    /**
     * Count collection column sum based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     * @abstract
     */
    abstract protected function _count_sum($index, $collection);
    /**
     * Count collection column average based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     * @abstract
     */
    abstract protected function _count_average($index, $collection);
    /**
     * Count collection column sum based on column index and expression
     *
     * @param string $index
     * @param string $expr
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _count($index, $expr, $collection)
    {
        switch ($expr) {
            case 'sum':
                $result = $this->_count_sum($index, $collection);
                break;
            case 'avg':
                $result = $this->_count_average($index, $collection);
                break;
            default:
                $result = $this->_count_expr($expr, $collection);
                break;
        }
        $this->_totals[$index] = $result;
        return $result;
    }
    /**
     * Return counted expression accorded parsed string
     *
     * @param string $expr
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _count_expr($expr, $collection)
    {
        $parsed_expression = $this->_parser->parse_expression($expr);
        $result = $tmp_result = 0;
        $first_operand = $second_operand = null;
        foreach ($parsed_expression as $operand) {
            if ($this->_parser->is_operation($operand)) {
                $this->_check_operands_set($first_operand, $second_operand, $tmp_result, $result);
                $result = $this->_operate($first_operand, $second_operand, $operand);
                $first_operand = $second_operand = null;
            } else if (null === $first_operand) {
                $first_operand = $this->_check_operand($operand, $collection);
            } elseif (null === $second_operand) {
                $second_operand = $this->_check_operand($operand, $collection);
            }
        }
        return $result;
    }
    /**
     * Check if operands in not null and set operands values if they are empty
     *
     * @param float|int $firstOperand
     * @param float|int $secondOperand
     * @param float|int $tmpResult
     * @param float|int $result
     * @return void
     */
    protected function _check_operands_set(&$first_operand, &$second_operand, &$tmp_result, $result)
    {
        if (null === $first_operand && null === $second_operand) {
            $first_operand = $tmp_result;
            $second_operand = $result;
        } elseif (null !== $first_operand && null === $second_operand) {
            $second_operand = $result;
        } elseif (null !== $first_operand && null !== $second_operand) {
            $tmp_result = $result;
        }
    }
    /**
     * Get result of operation
     *
     * @param float|int $firstOperand
     * @param float|int $secondOperand
     * @param string $operation
     * @return float|int
     */
    protected function _operate($first_operand, $second_operand, $operation)
    {
        $result = 0;
        switch ($operation) {
            case '+':
                $result = $first_operand + $second_operand;
                break;
            case '-':
                $result = $first_operand - $second_operand;
                break;
            case '*':
                $result = $first_operand * $second_operand;
                break;
            case '/':
                $result = $second_operand ? $first_operand / $second_operand : $second_operand;
                break;
        }
        return $result;
    }
    /**
     * Check operand is numeric or has already counted
     *
     * @param string $operand
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _check_operand($operand, $collection)
    {
        if (!is_numeric($operand)) {
            if (isset($this->_totals[$operand])) {
                $operand = $this->_totals[$operand];
            } else {
                $operand = $this->_count($operand, $this->_columns[$operand], $collection);
            }
        } else {
            $operand *= 1;
        }
        return $operand;
    }
    /**
     * Fill columns
     *
     * @param string $index
     * @param string $totalExpr
     * @return $this
     */
    public function set_column($index, $total_expr)
    {
        $this->_columns[$index] = $total_expr;
        return $this;
    }
    /**
     * Return columns set
     *
     * @return array
     */
    public function get_columns()
    {
        return $this->_columns;
    }
    /**
     * Count totals for all columns set
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Framework\DataObject
     */
    public function count_totals($collection)
    {
        foreach ($this->_columns as $index => $expr) {
            $this->_count($index, $expr, $collection);
        }
        return $this->get_totals();
    }
    /**
     * Get totals as object
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_totals()
    {
        return $this->_factory->create($this->_totals);
    }
    /**
     * Reset totals and columns set
     *
     * @param bool $isFullReset
     * @return void
     */
    public function reset($is_full_reset = false)
    {
        if ($is_full_reset) {
            $this->_columns = [];
        }
        $this->_totals = [];
    }
}