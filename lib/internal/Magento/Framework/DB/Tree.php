<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB;

use Magento\Framework\DB\Tree\Node;
use Magento\Framework\DB\Tree\Node_Set;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Magento Library
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 *
 * @deprecated 102.0.0 Not used anymore.
 */
class Tree
{
    /**
     * @var string|int
     */
    private $_id;
    /**
     * @var int
     */
    private $_left;
    /**
     * @var int
     */
    private $_right;
    /**
     * @var int
     */
    private $_level;
    /**
     * @var int
     */
    private $_pid;
    /**
     * @var array
     */
    private $_nodes_info = [];
    /**
     * Array of additional tables
     *
     * array(
     *  [$tableName] => array(
     *              ['joinCondition']
     *              ['fields']
     *          )
     * )
     *
     * @var array
     */
    private $_ext_tables = [];
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $_db;
    /**
     * @var string
     */
    private $_table;
    /**
     * @param array $config
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function __construct($config = [])
    {
        // set a \Zend_Db_Adapter connection
        if (!empty($config['db'])) {
            // convenience variable
            $connection = $config['db'];
            // use an object from the registry?
            if (is_string($connection)) {
                /** @phpstan-ignore-next-line */
                $connection = \Zend::registry($connection);
            }
            // make sure it's a \Magento\Framework\DB\Adapter\AdapterInterface
            if (!$connection instanceof \Magento\Framework\DB\Adapter\Adapter_Interface) {
                throw new Localized_Exception(new Phrase('db object does not implement \Magento\Framework\DB\Adapter\AdapterInterface'));
            }
            // save the connection
            $this->_db = $connection;
            $conn = $this->_db->get_connection();
            if ($conn instanceof \PDO) {
                $conn->set_attribute(\PDO::ATTR_EMULATE_PREPARES, true);
            }
        } else {
            throw new Localized_Exception(new Phrase('The "db object" isn\'t set in config. Set the "db object" and try again.'));
        }
        if (!empty($config['table'])) {
            $this->set_table($config['table']);
        }
        if (!empty($config['id'])) {
            $this->set_id_field($config['id']);
        } else {
            $this->set_id_field('id');
        }
        if (!empty($config['left'])) {
            $this->set_left_field($config['left']);
        } else {
            $this->set_left_field('left_key');
        }
        if (!empty($config['right'])) {
            $this->set_right_field($config['right']);
        } else {
            $this->set_right_field('right_key');
        }
        if (!empty($config['level'])) {
            $this->set_level_field($config['level']);
        } else {
            $this->set_level_field('level');
        }
        if (!empty($config['pid'])) {
            $this->set_pid_field($config['pid']);
        } else {
            $this->set_pid_field('parent_id');
        }
    }
    /**
     * set name of id field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_id_field($name)
    {
        $this->_id = $name;
        return $this;
    }
    /**
     * set name of left field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_left_field($name)
    {
        $this->_left = $name;
        return $this;
    }
    /**
     * set name of right field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_right_field($name)
    {
        $this->_right = $name;
        return $this;
    }
    /**
     * set name of level field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_level_field($name)
    {
        $this->_level = $name;
        return $this;
    }
    /**
     * set name of pid Field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_pid_field($name)
    {
        $this->_pid = $name;
        return $this;
    }
    /**
     * set table name
     *
     * @param string $name
     * @return $this
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function set_table($name)
    {
        $this->_table = $name;
        return $this;
    }
    /**
     * @return array
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function get_keys()
    {
        $keys = [];
        $keys['id'] = $this->_id;
        $keys['left'] = $this->_left;
        $keys['right'] = $this->_right;
        $keys['pid'] = $this->_pid;
        $keys['level'] = $this->_level;
        return $keys;
    }
    /**
     * Clear table and add root element
     *
     * @param array $data
     * @return string
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function clear($data = [])
    {
        // clearing table
        $this->_db->query('TRUNCATE ' . $this->_table);
        // prepare data for root element
        $data[$this->_pid] = 0;
        $data[$this->_left] = 1;
        $data[$this->_right] = 2;
        $data[$this->_level] = 0;
        try {
            $this->_db->insert($this->_table, $data);
        } catch (\PDOException $e) {
            echo $e->get_message();
        }
        return $this->_db->last_insert_id();
    }
    /**
     * Get node information
     *
     * @param string|int $nodeId
     * @return array
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function get_node_info($node_id)
    {
        if (empty($this->_nodes_info[$node_id])) {
            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE ' . $this->_id . '=:id';
            $res = $this->_db->query($sql, ['id' => $node_id]);
            $data = $res->fetch();
            $this->_nodes_info[$node_id] = $data;
        } else {
            $data = $this->_nodes_info[$node_id];
        }
        return $data;
    }
    /**
     * @param string|int $nodeId
     * @param array $data
     * @return false|string
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function append_child($node_id, $data)
    {
        $info = $this->get_node_info($node_id);
        if (!$info) {
            return false;
        }
        $data[$this->_left] = $info[$this->_right];
        $data[$this->_right] = $info[$this->_right] + 1;
        $data[$this->_level] = $info[$this->_level] + 1;
        $data[$this->_pid] = $node_id;
        // creating a place for the record being inserted
        if ($node_id) {
            $this->_db->begin_transaction();
            try {
                $sql = 'UPDATE ' . $this->_table . ' SET' . ' `' . $this->_left . '` = IF( `' . $this->_left . '` > :left,' . ' `' . $this->_left . '`+2, `' . $this->_left . '`),' . ' `' . $this->_right . '` = IF( `' . $this->_right . '`>= :right,' . ' `' . $this->_right . '`+2, `' . $this->_right . '`)' . ' WHERE `' . $this->_right . '` >= :right';
                $this->_db->query($sql, ['left' => $info[$this->_left], 'right' => $info[$this->_right]]);
                $this->_db->insert($this->_table, $data);
                $this->_db->commit();
            } catch (\PDOException $p) {
                $this->_db->roll_back();
                echo $p->get_message();
                exit;
            } catch (\Exception $e) {
                $this->_db->roll_back();
                echo $e->get_message();
                /** @phpstan-ignore-next-line */
                echo $sql;
                exit;
            }
            // TODO: change to ZEND LIBRARY
            $res = $this->_db->fetch_one('select last_insert_id()');
            return $res;
        }
        return false;
    }
    /**
     * @return array
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function check_nodes()
    {
        $sql = $this->_db->select();
        $sql->from(['t1' => $this->_table], ['t1.' . $this->_id, new \Zend_Db_Expr('COUNT(t1.' . $this->_id . ') AS rep')])->from(['t2' => $this->_table])->from(['t3' => $this->_table], new \Zend_Db_Expr('MAX(t3.' . $this->_right . ') AS max_right'));
        $sql->where('t1.' . $this->_left . ' <> t2.' . $this->_left)->where('t1.' . $this->_left . ' <> t2.' . $this->_right)->where('t1.' . $this->_right . ' <> t2.' . $this->_right);
        $sql->group('t1.' . $this->_id);
        $sql->having('max_right <> SQRT(4 * rep + 1) + 1');
        return $this->_db->fetch_all($sql);
    }
    /**
     * @param string|int $nodeId
     * @return bool|Node|void
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function remove_node($node_id)
    {
        $info = $this->get_node_info($node_id);
        if (!$info) {
            return false;
        }
        if ($node_id) {
            $this->_db->begin_transaction();
            try {
                /**
                 * DELETE FROM my_tree WHERE left_key >= $left_key AND right_key <= $right_key
                 */
                $this->_db->delete($this->_table, $this->_left . ' >= ' . $info[$this->_left] . ' AND ' . $this->_right . ' <= ' . $info[$this->_right]);
                /**
                 * UPDATE my_tree SET left_key = IF(left_key > $left_key, left_key – ($right_key - $left_key + 1),
                 *      left_key), right_key = right_key – ($right_key - $left_key + 1) WHERE right_key > $right_key
                 */
                $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_left . ' = IF(' . $this->_left . ' > ' . $info[$this->_left] . ', ' . $this->_left . ' - ' . ($info[$this->_right] - $info[$this->_left] + 1) . ', ' . $this->_left . '), ' . $this->_right . ' = ' . $this->_right . ' - ' . ($info[$this->_right] - $info[$this->_left] + 1) . ' WHERE ' . $this->_right . ' > ' . $info[$this->_right];
                $this->_db->query($sql);
                $this->_db->commit();
                return new Node($info, $this->get_keys());
            } catch (\Exception $e) {
                $this->_db->roll_back();
                echo $e->get_message();
            }
        }
    }
    /**
     * @param string|int $eId
     * @param string|int $pId
     * @param string|int $aId
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function move_node($e_id, $p_id, $a_id = 0)
    {
        $e_info = $this->get_node_info($e_id);
        $p_info = $this->get_node_info($p_id);
        $left_id = $e_info[$this->_left];
        $right_id = $e_info[$this->_right];
        $level = $e_info[$this->_level];
        $left_id_p = $p_info[$this->_left];
        $right_id_p = $p_info[$this->_right];
        $level_p = $p_info[$this->_level];
        if ($e_id == $p_id || $left_id == $left_id_p || $left_id_p >= $left_id && $left_id_p <= $right_id || $level == $level_p + 1 && $left_id > $left_id_p && $right_id < $right_id_p) {
            echo "alert('cant_move_tree');";
            return false;
        }
        if ($left_id_p < $left_id && $right_id_p > $right_id && $level_p < $level - 1) {
            $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_level . sprintf('%+d', -($level - 1) + $level_p) . ' ELSE ' . $this->_level . ' END, ' . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_id_p - 1) . ' THEN ' . $this->_right . '-' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_right . '+' . (($right_id_p - $right_id - $level + $level_p) / 2 * 2 + $level - $level_p - 1) . ' ELSE ' . $this->_right . ' END, ' . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_id_p - 1) . ' THEN ' . $this->_left . '-' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_left . '+' . (($right_id_p - $right_id - $level + $level_p) / 2 * 2 + $level - $level_p - 1) . ' ELSE ' . $this->_left . ' END ' . 'WHERE ' . $this->_left . ' BETWEEN ' . ($left_id_p + 1) . ' AND ' . ($right_id_p - 1);
        } elseif ($left_id_p < $left_id) {
            $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_level . sprintf('%+d', -($level - 1) + $level_p) . ' ELSE ' . $this->_level . ' END, ' . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $right_id_p . ' AND ' . ($left_id - 1) . ' THEN ' . $this->_left . '+' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_left . '-' . ($left_id - $right_id_p) . ' ELSE ' . $this->_left . ' END, ' . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . $right_id_p . ' AND ' . $left_id . ' THEN ' . $this->_right . '+' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_right . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_right . '-' . ($left_id - $right_id_p) . ' ELSE ' . $this->_right . ' END ' . 'WHERE (' . $this->_left . ' BETWEEN ' . $left_id_p . ' AND ' . $right_id . ' ' . 'OR ' . $this->_right . ' BETWEEN ' . $left_id_p . ' AND ' . $right_id . ')';
        } else {
            $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_level . sprintf('%+d', -($level - 1) + $level_p) . ' ELSE ' . $this->_level . ' END, ' . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $right_id . ' AND ' . $right_id_p . ' THEN ' . $this->_left . '-' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_left . '+' . ($right_id_p - 1 - $right_id) . ' ELSE ' . $this->_left . ' END, ' . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_id_p - 1) . ' THEN ' . $this->_right . '-' . ($right_id - $left_id + 1) . ' ' . 'WHEN ' . $this->_right . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->_right . '+' . ($right_id_p - 1 - $right_id) . ' ELSE ' . $this->_right . ' END ' . 'WHERE (' . $this->_left . ' BETWEEN ' . $left_id . ' AND ' . $right_id_p . ' ' . 'OR ' . $this->_right . ' BETWEEN ' . $left_id . ' AND ' . $right_id_p . ')';
        }
        $this->_db->begin_transaction();
        try {
            $this->_db->query($sql);
            $this->_db->commit();
            echo "alert('node moved');";
            return true;
        } catch (\Exception $e) {
            $this->_db->roll_back();
            echo "alert('node not moved: fatal error');";
            echo $e->get_message();
            echo "<br>\r\n";
            echo $sql;
            echo "<br>\r\n";
            exit;
        }
    }
    /**
     * @param string|int $eId
     * @param string|int $pId
     * @param string|int $aId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function move_nodes($e_id, $p_id, $a_id = 0)
    {
        $e_info = $this->get_node_info($e_id);
        if ($p_id != 0) {
            $p_info = $this->get_node_info($p_id);
        }
        if ($a_id != 0) {
            $a_info = $this->get_node_info($a_id);
        }
        $level = $e_info[$this->_level];
        $left_key = $e_info[$this->_left];
        $right_key = $e_info[$this->_right];
        if ($p_id == 0) {
            $level_up = 0;
        } else {
            /** @phpstan-ignore-next-line */
            $level_up = $p_info[$this->_level];
        }
        $right_key_near = 0;
        $left_key_near = 0;
        if ($p_id == 0) {
            //move to root
            $right_key_near = $this->_db->fetch_one('SELECT MAX(' . $this->_right . ') FROM ' . $this->_table);
        } elseif ($a_id != 0 && $p_id == $e_info[$this->_pid]) {
            // if we have after ID
            /** @phpstan-ignore-next-line */
            $right_key_near = $a_info[$this->_right];
            /** @phpstan-ignore-next-line */
            $left_key_near = $a_info[$this->_left];
        } elseif ($a_id == 0 && $p_id == $e_info[$this->_pid]) {
            // if we do not have after ID
            /** @phpstan-ignore-next-line */
            $right_key_near = $p_info[$this->_left];
        } elseif ($p_id != $e_info[$this->_pid]) {
            /** @phpstan-ignore-next-line */
            $right_key_near = $p_info[$this->_right] - 1;
        }
        /** @phpstan-ignore-next-line */
        $skew_level = $p_info[$this->_level] - $e_info[$this->_level] + 1;
        $skew_tree = $e_info[$this->_right] - $e_info[$this->_left] + 1;
        echo "alert('" . $right_key_near . "');";
        if ($right_key_near > $right_key) {
            // up
            echo "alert('move up');";
            $skew_edit = $right_key_near - $left_key + 1;
            $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_right . ' = IF(' . $this->_left . ' >= ' . $e_info[$this->_left] . ', ' . $this->_right . ' + ' . $skew_edit . ', IF(' . $this->_right . ' < ' . $e_info[$this->_left] . ', ' . $this->_right . ' + ' . $skew_tree . ', ' . $this->_right . ')), ' . $this->_level . ' = IF(' . $this->_left . ' >= ' . $e_info[$this->_left] . ', ' . $this->_level . ' + ' . $skew_level . ', ' . $this->_level . '), ' . $this->_left . ' = IF(' . $this->_left . ' >= ' . $e_info[$this->_left] . ', ' . $this->_left . ' + ' . $skew_edit . ', IF(' . $this->_left . ' > ' . $right_key_near . ', ' . $this->_left . ' + ' . $skew_tree . ', ' . $this->_left . '))' . ' WHERE ' . $this->_right . ' > ' . $right_key_near . ' AND ' . $this->_left . ' < ' . $e_info[$this->_right];
        } elseif ($right_key_near < $right_key) {
            // down
            echo "alert('move down');";
            $skew_edit = $right_key_near - $left_key + 1 - $skew_tree;
            $sql = 'UPDATE ' . $this->_table . ' SET ' . $this->_left . ' = IF(' . $this->_right . ' <= ' . $right_key . ', ' . $this->_left . ' + ' . $skew_edit . ', IF(' . $this->_left . ' > ' . $right_key . ', ' . $this->_left . ' - ' . $skew_tree . ', ' . $this->_left . ')), ' . $this->_level . ' = IF(' . $this->_right . ' <= ' . $right_key . ', ' . $this->_level . ' + ' . $skew_level . ', ' . $this->_level . '), ' . $this->_right . ' = IF(' . $this->_right . ' <= ' . $right_key . ', ' . $this->_right . ' + ' . $skew_edit . ', IF(' . $this->_right . ' <= ' . $right_key_near . ', ' . $this->_right . ' - ' . $skew_tree . ', ' . $this->_right . '))' . ' WHERE ' . $this->_right . ' > ' . $left_key . ' AND ' . $this->_left . ' <= ' . $right_key_near;
        }
        $this->_db->begin_transaction();
        try {
            /** @phpstan-ignore-next-line */
            $this->_db->query($sql);
            $this->_db->commit();
        } catch (\Exception $e) {
            $this->_db->roll_back();
            echo $e->get_message();
            echo "<br>\r\n";
            /** @phpstan-ignore-next-line */
            echo $sql;
            echo "<br>\r\n";
            exit;
        }
        echo "alert('node added')";
    }
    /**
     * @param string $tableName
     * @param string $joinCondition
     * @param string $fields
     * @return void
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function add_table($table_name, $join_condition, $fields = '*')
    {
        $this->_ext_tables[$table_name] = ['joinCondition' => $join_condition, 'fields' => $fields];
    }
    /**
     * @param Select $select
     * @return void
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    protected function _add_ext_tables_to_select(Select &$select)
    {
        foreach ($this->_ext_tables as $table_name => $info) {
            $select->join_inner($table_name, $info['joinCondition'], $info['fields']);
        }
    }
    /**
     * @param string|int $nodeId
     * @param int $startLevel
     * @param int $endLevel
     * @return NodeSet
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function get_children($node_id, $start_level = 0, $end_level = 0)
    {
        try {
            $info = $this->get_node_info($node_id);
        } catch (\Exception $e) {
            echo $e->get_message();
            exit;
        }
        /** @phpstan-ignore-next-line */
        $db_select = new Select($this->_db);
        $db_select->from($this->_table)->where($this->_left . ' >= :left')->where($this->_right . ' <= :right')->order($this->_left);
        $this->_add_ext_tables_to_select($db_select);
        $data = [];
        $data['left'] = $info[$this->_left];
        $data['right'] = $info[$this->_right];
        if (!empty($start_level) && empty($end_level)) {
            $db_select->where($this->_level . ' = :minLevel');
            $data['minLevel'] = $info[$this->_level] + $start_level;
        }
        //echo $dbSelect->__toString();
        $data = $this->_db->fetch_all($db_select, $data);
        $node_set = new Node_Set();
        foreach ($data as $node) {
            $node_set->add_node(new Node($node, $this->get_keys()));
        }
        return $node_set;
    }
    /**
     * @param string|int $nodeId
     * @return Node
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    public function get_node($node_id)
    {
        /** @phpstan-ignore-next-line */
        $db_select = new Select($this->_db);
        $db_select->from($this->_table)->where($this->_table . '.' . $this->_id . ' >= :id');
        $this->_add_ext_tables_to_select($db_select);
        $data = [];
        $data['id'] = $node_id;
        $data = $this->_db->fetch_row($db_select, $data);
        return new Node($data, $this->get_keys());
    }
}