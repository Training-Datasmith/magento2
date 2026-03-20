<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection as NodeCollection;
/**
 * Data tree
 *
 * @api
 * @since 100.0.2
 * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
 */
class Tree
{
    /**
     * Nodes collection
     *
     * @var NodeCollection
     */
    protected $_nodes;
    /**
     * Initialize Tree
     */
    public function __construct()
    {
        $this->_nodes = new Node_Collection($this);
    }
    /**
     * Enter description here...
     *
     * @return \Magento\Framework\Data\Tree
     */
    public function get_tree()
    {
        return $this;
    }
    /**
     * Enter description here...
     *
     * @param Node $parentNode
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($parent_node = null)
    {
    }
    /**
     * Enter description here...
     *
     * @param int|string $nodeId
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load_node($node_id)
    {
    }
    /**
     * Append child
     *
     * @param array|Node $data
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return Node
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function append_child($data, $parent_node, $prev_node = null)
    {
        if (is_array($data)) {
            $node = $this->add_node(new Node($data, $parent_node->get_id_field(), $this), $parent_node);
        } elseif ($data instanceof Node) {
            $node = $this->add_node($data, $parent_node);
        }
        return $node;
    }
    /**
     * Add node
     *
     * @param Node $node
     * @param Node $parent
     *
     * @return Node
     */
    public function add_node($node, $parent = null)
    {
        $this->_nodes->add($node);
        $node->set_parent($parent);
        if ($parent !== null && $parent instanceof Node) {
            $parent->add_child($node);
        }
        return $node;
    }
    /**
     * Move node
     *
     * @param Node $node
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function move_node_to($node, $parent_node, $prev_node = null)
    {
    }
    /**
     * Copy node
     *
     * @param Node $node
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function copy_node_to($node, $parent_node, $prev_node = null)
    {
    }
    /**
     * Remove node
     *
     * @param Node $node
     *
     * @return $this
     */
    public function remove_node($node)
    {
        $this->_nodes->delete($node);
        if ($node->get_parent()) {
            $node->get_parent()->remove_child($node);
        }
        unset($node);
        return $this;
    }
    /**
     * Create node
     *
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create_node($parent_node, $prev_node = null)
    {
    }
    /**
     * Get child
     *
     * @param Node $node
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_child($node)
    {
    }
    /**
     * Get children
     *
     * @param Node $node
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_children($node)
    {
    }
    /**
     * Enter description here...
     *
     * @return NodeCollection
     */
    public function get_nodes()
    {
        return $this->_nodes;
    }
    /**
     * Enter description here...
     *
     * @param Node $nodeId
     *
     * @return Node
     */
    public function get_node_by_id($node_id)
    {
        return $this->_nodes->search_by_id($node_id);
    }
    /**
     * Get path
     *
     * @param Node $node
     *
     * @return array
     */
    public function get_path($node)
    {
        if ($node instanceof Node) {
        } elseif (is_numeric($node)) {
            if ($_node = $this->get_node_by_id($node)) {
                return $_node->get_path();
            }
        }
        return [];
    }
}