<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Tree;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node\Collection;
/**
 * Data tree node
 *
 * @api
 * @since 100.0.2
 */
class Node extends \Magento\Framework\Data_Object
{
    /**
     * Parent node
     *
     * @var Node
     */
    protected $_parent;
    /**
     * Main tree object
     *
     * @var Tree
     */
    protected $_tree;
    /**
     * @var Collection
     */
    protected $_child_nodes;
    /**
     * Node ID field name
     *
     * @var string
     */
    protected $_id_field;
    /**
     * @param array $data
     * @param string $idField
     * @param Tree $tree
     * @param Node $parent
     */
    public function __construct($data, $id_field, $tree, $parent = null)
    {
        $this->set_tree($tree);
        $this->set_parent($parent);
        $this->set_id_field($id_field);
        $this->set_data($data);
        $this->_child_nodes = new Collection($this);
    }
    /**
     * Retrieve node id
     *
     * @return mixed
     */
    public function get_id()
    {
        return $this->get_data($this->get_id_field());
    }
    /**
     * Set node id field name
     *
     * @param   string $idField
     *
     * @return  $this
     */
    public function set_id_field($id_field)
    {
        $this->_id_field = $id_field;
        return $this;
    }
    /**
     * Retrieve node id field name
     *
     * @return string
     */
    public function get_id_field()
    {
        return $this->_id_field;
    }
    /**
     * Set node tree object
     *
     * @param   Tree $tree
     *
     * @return  $this
     */
    public function set_tree(Tree $tree)
    {
        $this->_tree = $tree;
        return $this;
    }
    /**
     * Retrieve node tree object
     *
     * @return Tree
     */
    public function get_tree()
    {
        return $this->_tree;
    }
    /**
     * Set node parent
     *
     * @param   Node $parent
     *
     * @return  $this
     */
    public function set_parent($parent)
    {
        $this->_parent = $parent;
        return $this;
    }
    /**
     * Retrieve node parent
     *
     * @return Tree
     */
    public function get_parent()
    {
        return $this->_parent;
    }
    /**
     * Check node children
     *
     * @return bool
     */
    public function has_children()
    {
        return $this->_child_nodes->count() > 0;
    }
    /**
     * Set level
     *
     * @param mixed $level
     *
     * @return $this
     */
    public function set_level($level)
    {
        $this->set_data('level', $level);
        return $this;
    }
    /**
     * Set path ID
     *
     * @param mixed $path
     *
     * @return $this
     */
    public function set_path_id($path)
    {
        $this->set_data('path_id', $path);
        return $this;
    }
    /**
     * Seemingyly useless method
     *
     * @param Node $node
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    public function is_child_of($node)
    {
    }
    /**
     * Load node children
     *
     * @param   int  $recursionLevel
     *
     * @return  \Magento\Framework\Data\Tree\Node
     */
    public function load_children($recursion_level = 0)
    {
        $this->_tree->load($this, $recursion_level);
        return $this;
    }
    /**
     * Retrieve node children collection
     *
     * @return Collection
     */
    public function get_children()
    {
        return $this->_child_nodes;
    }
    /**
     * Get all child nodes
     *
     * @param array $nodes
     *
     * @return array
     */
    public function get_all_child_nodes(&$nodes = [])
    {
        foreach ($this->_child_nodes as $node) {
            $nodes[$node->get_id()] = $node;
            $node->get_all_child_nodes($nodes);
        }
        return $nodes;
    }
    /**
     * Get last child
     *
     * @return mixed
     */
    public function get_last_child()
    {
        return $this->_child_nodes->last_node();
    }
    /**
     * Add child node
     *
     * @param   Node $node
     *
     * @return  Node
     */
    public function add_child($node)
    {
        $this->_child_nodes->add($node);
        return $this;
    }
    /**
     * Append child
     *
     * @param Node $prevNode
     *
     * @return $this
     */
    public function append_child($prev_node = null)
    {
        $this->_tree->append_child($this, $prev_node);
        return $this;
    }
    /**
     * Move to
     *
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return $this
     */
    public function move_to($parent_node, $prev_node = null)
    {
        $this->_tree->move_node_to($this, $parent_node, $prev_node);
        return $this;
    }
    /**
     * Copy to
     *
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return $this
     */
    public function copy_to($parent_node, $prev_node = null)
    {
        $this->_tree->copy_node_to($this, $parent_node, $prev_node);
        return $this;
    }
    /**
     * Remove child
     *
     * @param Node $childNode
     *
     * @return $this
     */
    public function remove_child($child_node)
    {
        $this->_child_nodes->delete($child_node);
        return $this;
    }
    /**
     * Get path
     *
     * @param array $prevNodes
     *
     * @return array
     */
    public function get_path(&$prev_nodes = [])
    {
        if ($this->_parent) {
            $prev_nodes[] = $this;
            $this->_parent->get_path($prev_nodes);
        }
        return $prev_nodes;
    }
    /**
     * Get is active
     *
     * @return mixed
     */
    public function get_is_active()
    {
        return $this->_get_data('is_active');
    }
    /**
     * Get name
     *
     * @return mixed
     */
    public function get_name()
    {
        return $this->_get_data('name');
    }
}