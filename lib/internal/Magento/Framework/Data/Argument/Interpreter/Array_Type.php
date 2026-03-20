<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use InvalidArgumentException;
use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Object_Manager\Helper\Sort_Items as SortItemsHelper;
/**
 * Interpreter of array data type that supports arrays of unlimited depth
 */
class Array_Type implements Interpreter_Interface
{
    /**
     * Interpreter of individual array item
     *
     * @var InterpreterInterface
     */
    private Interpreter_Interface $item_interpreter;
    /**
     * @var SortItemsHelper
     */
    private Sort_Items_Helper $sort_items_helper;
    /**
     * @param InterpreterInterface $itemInterpreter
     * @param SortItemsHelper|null $sortItemsHelper
     */
    public function __construct(Interpreter_Interface $item_interpreter, ?Sort_Items_Helper $sort_items_helper = null)
    {
        $this->item_interpreter = $item_interpreter;
        $this->sort_items_helper = $sort_items_helper ?: new \Magento\Framework\Object_Manager\Helper\Sort_Items();
    }
    /**
     * @inheritdoc
     * @return array
     * @throws InvalidArgumentException
     */
    public function evaluate(array $data): array
    {
        $items = $data['item'] ?? [];
        if (!is_array($items)) {
            throw new InvalidArgumentException('Array items are expected.');
        }
        $result = [];
        $items = $this->sort_items_helper->sort_items($items);
        foreach ($items as $item_key => $item_data) {
            $result[$item_key] = $this->item_interpreter->evaluate($item_data);
        }
        return $result;
    }
}