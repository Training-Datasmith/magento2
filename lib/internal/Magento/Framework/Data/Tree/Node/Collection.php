<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Tree\Node;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
/**
 * Tree node collection
 *
 * @api
 * @since 100.0.2
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $_nodes;
    /**
     * @var Node
     */
    private $_container;
    /**
     * @param Node $container
     */
    public function __construct($container)
    {
        $this->_nodes = [];
        $this->_container = $container;
    }
    /**
     * Get the nodes
     *
     * @return array
     */
    public function get_nodes()
    {
        return $this->_nodes;
    }
    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    #[\Return_Type_Will_Change]
    public function getIterator()
    {
        return new \ArrayIterator($this->_nodes);
    }
    /**
     * Implementation of \ArrayAccess:offsetSet()
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function offsetSet($key, $value)
    {
        $this->_nodes[$key] = $value;
    }
    /**
     * Implementation of \ArrayAccess:offsetGet()
     *
     * @param string $key
     * @return mixed
     */
    #[\Return_Type_Will_Change]
    public function offsetGet($key)
    {
        return $this->_nodes[$key];
    }
    /**
     * Implementation of \ArrayAccess:offsetUnset()
     *
     * @param string $key
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function offsetUnset($key)
    {
        unset($this->_nodes[$key]);
    }
    /**
     * Implementation of \ArrayAccess:offsetExists()
     *
     * @param string $key
     * @return bool
     */
    #[\Return_Type_Will_Change]
    public function offsetExists($key)
    {
        return isset($this->_nodes[$key]);
    }
    /**
     * Adds a node to this node
     *
     * @param Node $node
     * @return Node
     */
    public function add(Node $node)
    {
        $node->set_parent($this->_container);
        // Set the Tree for the node
        if ($this->_container->get_tree() instanceof Tree) {
            $node->set_tree($this->_container->get_tree());
        }
        $node_id = $node->get_id() ?? '';
        $this->_nodes[$node_id] = $node;
        return $node;
    }
    /**
     * Delete
     *
     * @param Node $node
     * @return $this
     */
    public function delete($node)
    {
        $node_id = $node->get_id() ?? '';
        if (isset($this->_nodes[$node_id])) {
            unset($this->_nodes[$node_id]);
        }
        return $this;
    }
    /**
     * Return count
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function count()
    {
        return count($this->_nodes);
    }
    /**
     * Return the last node
     *
     * @return mixed
     */
    public function last_node()
    {
        if (!empty($this->_nodes)) {
            $result = end($this->_nodes);
            reset($this->_nodes);
        } else {
            $result = null;
        }
        return $result;
    }
    /**
     * Search by Id
     *
     * @param string $nodeId
     * @return null
     */
    public function search_by_id($node_id)
    {
        $node_id = $node_id ?? '';
        if (isset($this->_nodes[$node_id])) {
            return $this->_nodes[$node_id];
        }
        return null;
    }
}