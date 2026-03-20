<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Graph data structure
 */
class Graph
{
    /**#@+
     * Search modes
     */
    public const DIRECTIONAL = 1;
    public const INVERSE = 2;
    public const NON_DIRECTIONAL = 3;
    /**#@-*/
    /**#@-*/
    protected $_nodes = [];
    /**
     * Declared relations directed "from" "to"
     *
     * @var array
     */
    protected $_from = [];
    /**
     * Inverse relations "to" "from"
     *
     * @var array
     */
    protected $_to = [];
    /**
     * Validate consistency of the declared structure and assign it to the object state
     *
     * @param array $nodes plain array with node identifiers
     * @param array $relations array of 2-item plain arrays, which represent relations of nodes "from" "to"
     */
    public function __construct(array $nodes, array $relations)
    {
        foreach ($nodes as $node) {
            $this->_assert_node($node, false);
            $this->_nodes[$node] = $node;
        }
        foreach ($relations as $pair) {
            list($from_node, $to_node) = $pair;
            $this->add_relation($from_node, $to_node);
        }
    }
    /**
     * Set a relation between nodes
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function add_relation($from_node, $to_node)
    {
        if ($from_node == $to_node) {
            throw new \InvalidArgumentException("Graph node '{$from_node}' is linked to itself.");
        }
        $this->_assert_node($from_node, true);
        $this->_assert_node($to_node, true);
        $this->_from[$from_node][$to_node] = $to_node;
        $this->_to[$to_node][$from_node] = $from_node;
        return $this;
    }
    /**
     * Export relations between nodes. Can return inverse relations
     *
     * @param int $mode
     * @return array
     * @throws \InvalidArgumentException
     */
    public function get_relations($mode = self::DIRECTIONAL)
    {
        switch ($mode) {
            case self::DIRECTIONAL:
                return $this->_from;
            case self::INVERSE:
                return $this->_to;
            case self::NON_DIRECTIONAL:
                $graph = $this->_from;
                foreach ($this->_to as $id_to => $relations) {
                    foreach ($relations as $id_from) {
                        $graph[$id_to][$id_from] = $id_from;
                    }
                }
                return $graph;
            default:
                throw new \InvalidArgumentException("Unknown search mode: '{$mode}'");
        }
    }
    /**
     * Find a cycle in the graph
     *
     * Returns first/all found cycle
     * Optionally may specify a node to return a cycle if it is in any
     *
     * @param string|int $node
     * @param boolean $firstOnly found only first cycle
     * @return array
     */
    public function find_cycle($node = null, $first_only = true)
    {
        $nodes = null === $node ? $this->_nodes : [$node];
        $results = [];
        foreach ($nodes as $node) {
            $result = $this->dfs($node, $node);
            if ($result) {
                if ($first_only) {
                    return $result;
                } else {
                    $results[] = $result;
                }
            }
        }
        return $results;
    }
    /**
     * Find paths to reachable nodes from root node
     *
     * Returns array of paths, key is destination node and value is path (an array) from rootNode to destination node
     * Eg. dest => [root, A, dest] means root -> A -> dest
     *
     * @param string|int $rootNode
     * @param int $mode
     * @return array
     */
    public function find_paths_to_reachable_nodes($root_node, $mode = self::DIRECTIONAL)
    {
        $graph = $this->get_relations($mode);
        $paths = [];
        $queue = [$root_node];
        $visited = [$root_node => $root_node];
        $paths[$root_node] = [$root_node];
        while (!empty($queue)) {
            $node = array_shift($queue);
            if (!empty($graph[$node])) {
                foreach ($graph[$node] as $child) {
                    if (!isset($visited[$child])) {
                        $paths[$child] = $paths[$node];
                        $paths[$child][] = $child;
                        $visited[$child] = $child;
                        $queue[] = $child;
                    }
                }
            }
        }
        return $paths;
    }
    /**
     * "Depth-first search" of a path between nodes
     *
     * Returns path as array of nodes or empty array if path does not exist.
     * Only first found path is returned. It will be not necessary the shortest or optimal in any way.
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @param int $mode
     * @return array
     */
    public function dfs($from_node, $to_node, $mode = self::DIRECTIONAL)
    {
        $this->_assert_node($from_node, true);
        $this->_assert_node($to_node, true);
        return $this->_dfs($from_node, $to_node, $this->get_relations($mode));
    }
    /**
     * Recursive sub-routine of dfs()
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @param array $graph
     * @param array &$visited
     * @param array $stack
     * @return array
     * @link http://en.wikipedia.org/wiki/Depth-first_search
     */
    protected function _dfs($from_node, $to_node, $graph, &$visited = [], $stack = [])
    {
        $stack[] = $from_node;
        $visited[$from_node] = $from_node;
        if (isset($graph[$from_node][$to_node])) {
            $stack[] = $to_node;
            return $stack;
        }
        if (isset($graph[$from_node])) {
            foreach ($graph[$from_node] as $node) {
                if (!isset($visited[$node])) {
                    $result = $this->_dfs($node, $to_node, $graph, $visited, $stack);
                    if ($result) {
                        return $result;
                    }
                }
            }
        }
        return [];
    }
    /**
     * Verify existence or non-existence of a node
     *
     * @param string|int $node
     * @param bool $mustExist
     * @return void
     * @throws \InvalidArgumentException according to assertion rules
     */
    protected function _assert_node($node, $must_exist)
    {
        if (isset($this->_nodes[$node])) {
            if (!$must_exist) {
                throw new \InvalidArgumentException("Graph node '{$node}' already exists'.");
            }
        } else if ($must_exist) {
            throw new \InvalidArgumentException("Graph node '{$node}' does not exist.");
        }
    }
}