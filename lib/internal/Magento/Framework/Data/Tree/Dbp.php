<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Tree;

use Magento\Framework\DB\Select;
/**
 * Data DB tree
 *
 * Data model:
 * id  |  path  |  order
 */
class Dbp extends \Magento\Framework\Data\Tree
{
    public const ID_FIELD = 'id';
    public const PATH_FIELD = 'path';
    public const ORDER_FIELD = 'order';
    public const LEVEL_FIELD = 'level';
    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_conn;
    /**
     * Data table name
     *
     * @var string
     */
    protected $_table;
    /**
     * Indicates if loaded
     *
     * @var bool
     */
    protected $_loaded = false;
    /**
     * SQL select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;
    /**
     * Tree structure field: id
     *
     * @var string
     */
    protected $_id_field;
    /**
     * Tree structure field: path
     *
     * @var string
     */
    protected $_path_field;
    /**
     * Tree structure field: order
     *
     * @var string
     */
    protected $_order_field;
    /**
     * Tree structure field: level
     *
     * @var string
     */
    protected $_level_field;
    /**
     * Db tree constructor
     *
     * $fields = array(
     *      \Magento\Framework\Data\Tree\Dbp::ID_FIELD       => string,
     *      \Magento\Framework\Data\Tree\Dbp::PATH_FIELD     => string,
     *      \Magento\Framework\Data\Tree\Dbp::ORDER_FIELD    => string
     *      \Magento\Framework\Data\Tree\Dbp::LEVEL_FIELD    => string
     * )
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $table
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(\Magento\Framework\DB\Adapter\Adapter_Interface $connection, $table, $fields)
    {
        parent::__construct();
        if (!$connection) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Wrong "$connection" parametr');
        }
        $this->_conn = $connection;
        $this->_table = $table;
        if (!isset($fields[self::ID_FIELD]) || !isset($fields[self::PATH_FIELD]) || !isset($fields[self::LEVEL_FIELD]) || !isset($fields[self::ORDER_FIELD])) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('"$fields" tree configuratin array');
        }
        $this->_id_field = $fields[self::ID_FIELD];
        $this->_path_field = $fields[self::PATH_FIELD];
        $this->_order_field = $fields[self::ORDER_FIELD];
        $this->_level_field = $fields[self::LEVEL_FIELD];
        $this->_select = $this->_conn->select();
        $this->_select->from($this->_table);
    }
    /**
     * Retrieve current select object
     *
     * @return Select
     */
    public function get_db_select()
    {
        return $this->_select;
    }
    /**
     * Set Select object
     *
     * @param Select $select
     * @return void
     */
    public function set_db_select($select)
    {
        $this->_select = $select;
    }
    /**
     * Load tree
     *
     * @param   int|Node|string $parentNode
     * @param   int $recursionLevel
     * @return  $this
     */
    public function load($parent_node = null, $recursion_level = 0)
    {
        if (!$this->_loaded) {
            $start_level = 1;
            $parent_path = '';
            if ($parent_node instanceof Node) {
                $parent_path = $parent_node->get_data($this->_path_field);
                $start_level = $parent_node->get_data($this->_level_field);
            } elseif (is_numeric($parent_node)) {
                $select = $this->_conn->select()->from($this->_table, [$this->_path_field, $this->_level_field])->where("{$this->_id_field} = ?", $parent_node);
                $parent = $this->_conn->fetch_row($select);
                $start_level = $parent[$this->_level_field];
                $parent_path = $parent[$this->_path_field];
                $parent_node = null;
            } elseif (is_string($parent_node)) {
                $parent_path = $parent_node;
                $start_level = count(explode(',', $parent_path)) - 1;
                $parent_node = null;
            }
            $select = clone $this->_select;
            $select->order($this->_table . '.' . $this->_order_field . ' ASC');
            if ($parent_path) {
                $path_field = $this->_conn->quote_identifier([$this->_table, $this->_path_field]);
                $select->where("{$path_field} LIKE ?", "{$parent_path}/%");
            }
            if ($recursion_level != 0) {
                $level_field = $this->_conn->quote_identifier([$this->_table, $this->_level_field]);
                $select->where("{$level_field} <= ?", $start_level + $recursion_level);
            }
            $arr_nodes = $this->_conn->fetch_all($select);
            $children_items = [];
            foreach ($arr_nodes as $node_info) {
                $path_to_parent = explode('/', $node_info[$this->_path_field] ?? '');
                array_pop($path_to_parent);
                $path_to_parent = implode('/', $path_to_parent);
                $children_items[$path_to_parent][] = $node_info;
            }
            $this->add_child_nodes($children_items, $parent_path, $parent_node);
            $this->_loaded = true;
        }
        return $this;
    }
    /**
     * Add child nodes
     *
     * @param array $children
     * @param string $path
     * @param Node $parentNode
     * @param int $level
     * @return void
     */
    public function add_child_nodes($children, $path, $parent_node, $level = 0)
    {
        // PHP 8.5 Compatibility: Check for null before using as array offset
        if ($path !== null && isset($children[$path])) {
            foreach ($children[$path] as $child) {
                $node_id = isset($child[$this->_id_field]) ? $child[$this->_id_field] : false;
                if ($parent_node && $node_id && $node = $parent_node->get_children()->search_by_id($node_id)) {
                    $node->add_data($child);
                } else {
                    $node = new Node($child, $this->_id_field, $this, $parent_node);
                }
                //$node->setLevel(count(explode('/', $node->getData($this->_pathField)))-1);
                $node->set_level($node->get_data($this->_level_field));
                $node->set_path_id($node->get_data($this->_path_field));
                $this->add_node($node, $parent_node);
                if ($path) {
                    $children_path = explode('/', $path);
                } else {
                    $children_path = [];
                }
                $children_path[] = $node->get_id();
                $children_path = implode('/', $children_path);
                $this->add_child_nodes($children, $children_path, $node, $level + 1);
            }
        }
    }
    /**
     * Load node
     *
     * @param int|string $nodeId
     * @return Node
     */
    public function load_node($node_id)
    {
        $select = clone $this->_select;
        if (is_numeric($node_id)) {
            $cond_field = $this->_conn->quote_identifier([$this->_table, $this->_id_field]);
        } else {
            $cond_field = $this->_conn->quote_identifier([$this->_table, $this->_path_field]);
        }
        $select->where("{$cond_field} = ?", $node_id);
        $node = new Node($this->_conn->fetch_row($select), $this->_id_field, $this);
        $this->add_node($node);
        return $node;
    }
    /**
     * Get children
     *
     * @param Node $node
     * @param bool $recursive
     * @param array $result
     * @return array
     */
    public function get_children($node, $recursive = true, $result = [])
    {
        if (is_numeric($node)) {
            $node = $this->get_node_by_id($node);
        }
        if (!$node) {
            return $result;
        }
        foreach ($node->get_children() as $child) {
            if ($recursive) {
                if ($child->get_children()) {
                    $result = $this->get_children($child, $recursive, $result);
                }
            }
            $result[] = $child->get_id();
        }
        return $result;
    }
    /**
     * Move tree node
     *
     * @param Node $node
     * @param Node $newParent
     * @param Node $prevNode
     * @return void
     * @throws \Exception
     * @todo Use adapter for generate conditions
     */
    public function move($node, $new_parent, $prev_node = null)
    {
        $position = 1;
        $old_path = $node->get_data($this->_path_field);
        $new_path = $new_parent->get_data($this->_path_field);
        $new_path = $new_path . '/' . $node->get_id();
        $old_path_length = $old_path !== null ? strlen($old_path) : 0;
        $new_level = $new_parent->get_level() + 1;
        $level_disposition = $new_level - $node->get_level();
        $data = [$this->_level_field => new \Zend_Db_Expr("{$this->_level_field} + '{$level_disposition}'"), $this->_path_field => new \Zend_Db_Expr("CONCAT('{$new_path}', RIGHT({$this->_path_field}, LENGTH({$this->_path_field}) - {$old_path_length}))")];
        $condition = $this->_conn->quote_into("{$this->_path_field} REGEXP ?", "^{$old_path}(/|\$)");
        $this->_conn->begin_transaction();
        $reorder_data = [$this->_order_field => new \Zend_Db_Expr("{$this->_order_field} + 1")];
        try {
            if ($prev_node && $prev_node->get_id()) {
                $reorder_condition = "{$this->_order_field} > {$prev_node->get_data($this->_order_field)}";
                $position = $prev_node->get_data($this->_order_field) + 1;
            } else {
                $reorder_condition = $this->_conn->quote_into("{$this->_path_field} REGEXP ?", "^{$new_parent->get_data($this->_path_field)}/[0-9]+\$");
                $select = $this->_conn->select()->from($this->_table, new \Zend_Db_Expr("MIN({$this->_order_field})"))->where($reorder_condition);
                $position = (int) $this->_conn->fetch_one($select);
            }
            $this->_conn->update($this->_table, $reorder_data, $reorder_condition);
            $this->_conn->update($this->_table, $data, $condition);
            $this->_conn->update($this->_table, [$this->_order_field => $position, $this->_level_field => $new_level], $this->_conn->quote_into("{$this->_id_field} = ?", $node->get_id()));
            $this->_conn->commit();
        } catch (\Exception $e) {
            $this->_conn->roll_back();
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Can't move tree node due to error: " . $e->get_message());
        }
    }
    /**
     * Load ensured nodes
     *
     * @param object $category
     * @param Node $rootNode
     * @return void
     */
    public function load_ensured_nodes($category, $root_node)
    {
        $path_ids = $category->get_path_ids();
        $root_node_id = $root_node->get_id();
        $root_node_path = $root_node->get_data($this->_path_field);
        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_order_field . ' ASC');
        if ($path_ids) {
            $condition = $this->_conn->quote_into("{$this->_table}.{$this->_id_field} in (?)", $path_ids);
            $select->where($condition);
        }
        $arr_nodes = $this->_conn->fetch_all($select);
        if ($arr_nodes) {
            $children_items = [];
            foreach ($arr_nodes as $node_info) {
                $node_id = $node_info[$this->_id_field];
                if ($node_id <= $root_node_id) {
                    continue;
                }
                $path_to_parent = explode('/', $node_info[$this->_path_field] ?? '');
                array_pop($path_to_parent);
                $path_to_parent = implode('/', $path_to_parent);
                $children_items[$path_to_parent][] = $node_info;
            }
            $this->_add_child_nodes($children_items, $root_node_path, $root_node, true);
        }
    }
    /**
     * Add child nodes
     *
     * @param array $children
     * @param string $path
     * @param Node $parentNode
     * @param bool $withChildren
     * @param int $level
     * @return void
     */
    protected function _add_child_nodes($children, $path, $parent_node, $with_children = false, $level = 0)
    {
        if (isset($children[$path])) {
            foreach ($children[$path] as $child) {
                $node_id = isset($child[$this->_id_field]) ? $child[$this->_id_field] : false;
                if ($parent_node && $node_id && $node = $parent_node->get_children()->search_by_id($node_id)) {
                    $node->add_data($child);
                } else {
                    $node = new Node($child, $this->_id_field, $this, $parent_node);
                    $node->set_level($node->get_data($this->_level_field));
                    $node->set_path_id($node->get_data($this->_path_field));
                    $this->add_node($node, $parent_node);
                }
                if ($with_children) {
                    $this->_loaded = false;
                    $node->load_children(1);
                    $this->_loaded = false;
                }
                if ($path) {
                    $children_path = explode('/', $path);
                } else {
                    $children_path = [];
                }
                $children_path[] = $node->get_id();
                $children_path = implode('/', $children_path);
                $this->_add_child_nodes($children, $children_path, $node, $with_children, $level + 1);
            }
        }
    }
}