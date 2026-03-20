<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Tree;

/**
 * Data DB tree
 *
 * Data model:
 * id  |  pid  |  level | order
 */
class Db extends \Magento\Framework\Data\Tree
{
    public const ID_FIELD = 'id';
    public const PARENT_FIELD = 'parent';
    public const LEVEL_FIELD = 'level';
    public const ORDER_FIELD = 'order';
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
     * SQL select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;
    /**
     * Tree structure field name: _idField
     *
     * @var string
     */
    protected $_id_field;
    /**
     * Tree structure field name: _parentField
     *
     * @var string
     */
    protected $_parent_field;
    /**
     * Tree structure field name: _levelField
     *
     * @var string
     */
    protected $_level_field;
    /**
     * Tree structure field name: _orderField
     *
     * @var string
     */
    protected $_order_field;
    /**
     * $fields = array(
     *      \Magento\Framework\Data\Tree\Db::ID_FIELD       => string,
     *      \Magento\Framework\Data\Tree\Db::PARENT_FIELD   => string,
     *      \Magento\Framework\Data\Tree\Db::LEVEL_FIELD    => string
     *      \Magento\Framework\Data\Tree\Db::ORDER_FIELD    => string
     * )
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $table
     * @param array $fields
     *
     * @throws \Exception
     */
    public function __construct(\Magento\Framework\DB\Adapter\Adapter_Interface $connection, $table, $fields)
    {
        parent::__construct();
        if (!$connection) {
            throw new \Exception('Wrong "$connection" parametr');
            // phpcs:ignore
        }
        $this->_conn = $connection;
        $this->_table = $table;
        if (!isset($fields[self::ID_FIELD]) || !isset($fields[self::PARENT_FIELD]) || !isset($fields[self::LEVEL_FIELD]) || !isset($fields[self::ORDER_FIELD])) {
            throw new \Exception('"$fields" tree configuratin array');
            // phpcs:ignore
        }
        $this->_id_field = $fields[self::ID_FIELD];
        $this->_parent_field = $fields[self::PARENT_FIELD];
        $this->_level_field = $fields[self::LEVEL_FIELD];
        $this->_order_field = $fields[self::ORDER_FIELD];
        $this->_select = $this->_conn->select();
        $this->_select->from($this->_table, array_values($fields));
    }
    /**
     * Get database select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function get_db_select()
    {
        return $this->_select;
    }
    /**
     * Set database select
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return void
     */
    public function set_db_select($select)
    {
        $this->_select = $select;
    }
    /**
     * Load tree
     *
     * @param int|Node $parentNode
     * @param int $recursionLevel
     *
     * @return $this
     * @throws \Exception
     */
    public function load($parent_node = null, $recursion_level = 100)
    {
        if ($parent_node === null) {
            $this->_load_full_tree();
            return $this;
        } elseif ($parent_node instanceof Node) {
            $parent_id = $parent_node->get_id();
        } elseif (is_numeric($parent_node)) {
            $parent_id = $parent_node;
            $parent_node = null;
        } else {
            throw new \Exception('root node id is not defined');
            // phpcs:ignore
        }
        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_order_field . ' ASC');
        $condition = $this->_conn->quote_into("{$this->_table}.{$this->_parent_field}=?", $parent_id);
        $select->where($condition);
        $arr_nodes = $this->_conn->fetch_all($select);
        foreach ($arr_nodes as $node_info) {
            $node = new Node($node_info, $this->_id_field, $this, $parent_node);
            $this->add_node($node, $parent_node);
            if ($recursion_level) {
                $node->load_children($recursion_level - 1);
            }
        }
        return $this;
    }
    /**
     * Load node
     *
     * @param mixed $nodeId
     *
     * @return Node
     */
    public function load_node($node_id)
    {
        $select = clone $this->_select;
        $condition = $this->_conn->quote_into("{$this->_table}.{$this->_id_field}=?", $node_id);
        $select->where($condition);
        $node = new Node($this->_conn->fetch_row($select), $this->_id_field, $this);
        $this->add_node($node);
        return $node;
    }
    /**
     * Append child
     *
     * @param Node $data
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return Node
     */
    public function append_child($data, $parent_node, $prev_node = null)
    {
        $order_select = $this->_conn->select();
        $order_select->from($this->_table, new \Zend_Db_Expr('MAX(' . $this->_conn->quote_identifier($this->_order_field) . ')'))->where($this->_conn->quote_identifier($this->_parent_field) . '=' . $parent_node->get_id());
        $order = $this->_conn->fetch_one($order_select);
        $data[$this->_parent_field] = $parent_node->get_id();
        $data[$this->_level_field] = $parent_node->get_data($this->_level_field) + 1;
        $data[$this->_order_field] = $order + 1;
        $this->_conn->insert($this->_table, $data);
        $data[$this->_id_field] = $this->_conn->last_insert_id();
        return parent::append_child($data, $parent_node, $prev_node);
    }
    /**
     * Move tree node
     *
     * @param Node $node
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return void
     * @throws \Exception
     */
    public function move_node_to($node, $parent_node, $prev_node = null)
    {
        $data = [];
        $data[$this->_parent_field] = $parent_node->get_id();
        $data[$this->_level_field] = $parent_node->get_data($this->_level_field) + 1;
        // New node order
        if ($prev_node === null || $prev_node->get_data($this->_order_field) === null) {
            $data[$this->_order_field] = 1;
        } else {
            $data[$this->_order_field] = $prev_node->get_data($this->_order_field) + 1;
        }
        $condition = $this->_conn->quote_into("{$this->_id_field}=?", $node->get_id());
        // For reorder new node branch
        $data_reorder_new = [$this->_order_field => new \Zend_Db_Expr($this->_conn->quote_identifier($this->_order_field) . '+1')];
        $condition_reorder_new = $this->_conn->quote_identifier($this->_parent_field) . '=' . $parent_node->get_id() . ' AND ' . $this->_conn->quote_identifier($this->_order_field) . '>=' . $data[$this->_order_field];
        // For reorder old node branch
        $data_reorder_old = [$this->_order_field => new \Zend_Db_Expr($this->_conn->quote_identifier($this->_order_field) . '-1')];
        $condition_reorder_old = $this->_conn->quote_identifier($this->_parent_field) . '=' . $node->get_data($this->_parent_field) . ' AND ' . $this->_conn->quote_identifier($this->_order_field) . '>' . $node->get_data($this->_order_field);
        $this->_conn->begin_transaction();
        try {
            // Prepare new node branch
            $this->_conn->update($this->_table, $data_reorder_new, $condition_reorder_new);
            // Move node
            $this->_conn->update($this->_table, $data, $condition);
            // Update old node branch
            $this->_conn->update($this->_table, $data_reorder_old, $condition_reorder_old);
            $this->_update_child_levels($node->get_id(), $data[$this->_level_field]);
            $this->_conn->commit();
        } catch (\Exception $e) {
            $this->_conn->roll_back();
            throw new \Exception('Can\'t move tree node');
            // phpcs:ignore
        }
    }
    /**
     * Update child levels
     *
     * @param mixed $parentId
     * @param int $parentLevel
     *
     * @return $this
     */
    protected function _update_child_levels($parent_id, $parent_level)
    {
        $select = $this->_conn->select()->from($this->_table, $this->_id_field)->where($this->_parent_field . '=?', $parent_id);
        $ids = $this->_conn->fetch_col($select);
        if (!empty($ids)) {
            $this->_conn->update($this->_table, [$this->_level_field => $parent_level + 1], $this->_conn->quote_into($this->_id_field . ' IN (?)', $ids));
            foreach ($ids as $id) {
                $this->_update_child_levels($id, $parent_level + 1);
            }
        }
        return $this;
    }
    /**
     * Load full tree
     *
     * @return $this
     */
    protected function _load_full_tree()
    {
        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_level_field)->order($this->_table . '.' . $this->_order_field);
        $arr_nodes = $this->_conn->fetch_all($select);
        foreach ($arr_nodes as $node_info) {
            $node = new Node($node_info, $this->_id_field, $this);
            $parent_node = $this->get_node_by_id($node_info[$this->_parent_field]);
            $this->add_node($node, $parent_node);
        }
        return $this;
    }
    /**
     * Remove node
     *
     * @param Node $node
     *
     * @return $this
     * @throws \Exception
     */
    public function remove_node($node)
    {
        // For reorder old node branch
        $data_reorder_old = [$this->_order_field => new \Zend_Db_Expr($this->_conn->quote_identifier($this->_order_field) . '-1')];
        $condition_reorder_old = $this->_conn->quote_identifier($this->_parent_field) . '=' . $node->get_data($this->_parent_field) . ' AND ' . $this->_conn->quote_identifier($this->_order_field) . '>' . $node->get_data($this->_order_field);
        $this->_conn->begin_transaction();
        try {
            $condition = $this->_conn->quote_into("{$this->_id_field}=?", $node->get_id());
            $this->_conn->delete($this->_table, $condition);
            // Update old node branch
            $this->_conn->update($this->_table, $data_reorder_old, $condition_reorder_old);
            $this->_conn->commit();
        } catch (\Exception $e) {
            $this->_conn->roll_back();
            throw new \Exception('Can\'t remove tree node');
            // phpcs:ignore
        }
        parent::remove_node($node);
        return $this;
    }
}