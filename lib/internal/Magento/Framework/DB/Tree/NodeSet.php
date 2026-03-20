<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Tree;

/**
 * TODO implements iterators
 *
 * @deprecated 102.0.0 Not used anymore.
 */
class Node_Set implements \Iterator, \Countable
{
    /**
     * @var Node[]
     */
    private $_nodes;
    /**
     * @var int
     */
    private $_current;
    /**
     * @var int
     */
    private $_current_node;
    /**
     * @var int
     */
    private $count;
    /**
     * Constructor
     *
     * @deprecated 102.0.0
     */
    public function __construct()
    {
        $this->_nodes = [];
        $this->_current = 0;
        $this->_current_node = 0;
        $this->count = 0;
    }
    /**
     * Adds a node to node list.
     *
     * @param Node $node
     * @return int
     *
     * @deprecated 102.0.0
     */
    public function add_node(Node $node)
    {
        $this->_nodes[$this->_current_node] = $node;
        $this->count++;
        return ++$this->_current_node;
    }
    /**
     * Retrieves count elements in node list.
     *
     * @return int
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function count()
    {
        return $this->count;
    }
    /**
     * Checks if current position is valid.
     *
     * @return bool
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function valid()
    {
        return isset($this->_nodes[$this->_current]);
    }
    /**
     * Move forward to next element.
     *
     * @return false|int
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        if ($this->_current > $this->_current_node) {
            return false;
        } else {
            return $this->_current++;
        }
    }
    /**
     * Retrieves the key of the current element.
     *
     * @return int
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function key()
    {
        return $this->_current;
    }
    /**
     * Retrieves the current node.
     *
     * @return Node
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        return $this->_nodes[$this->_current];
    }
    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     *
     * @deprecated 102.0.0
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        $this->_current = 0;
    }
}