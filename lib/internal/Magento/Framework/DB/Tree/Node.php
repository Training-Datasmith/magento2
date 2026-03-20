<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Tree;

use Magento\Framework\Exception\Localized_Exception;
/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 *
 * @deprecated 102.0.0 Not used anymore.
 */
class Node
{
    /**
     * @var int
     */
    private $left;
    /**
     * @var int
     */
    private $right;
    /**
     * @var string|int
     */
    private $id;
    /**
     * @var string|int
     */
    private $pid;
    /**
     * @var int
     */
    private $level;
    /**
     * @var string
     */
    private $title;
    /**
     * @var array
     */
    private $data;
    /**
     * @var bool
     *
     * @deprecated 102.0.0
     */
    public $has_child = false;
    /**
     * @var float|int
     *
     * @deprecated 102.0.0
     */
    public $num_child = 0;
    /**
     * @param array $nodeData
     * @param array $keys
     * @throws LocalizedException
     *
     * @deprecated 102.0.0
     */
    public function __construct($node_data, $keys)
    {
        if (empty($node_data)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The node information is empty. Enter the information and try again.'));
        }
        if (empty($keys)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase("The encryption key can't be empty. Enter the key and try again."));
        }
        $this->id = $node_data[$keys['id']];
        $this->pid = $node_data[$keys['pid']];
        $this->left = $node_data[$keys['left']];
        $this->right = $node_data[$keys['right']];
        $this->level = $node_data[$keys['level']];
        $this->data = $node_data;
        $a = $this->right - $this->left;
        if ($a > 1) {
            $this->has_child = true;
            $this->num_child = ($a - 1) / 2;
        }
        return $this;
    }
    /**
     * @param string $name
     * @return null|array
     *
     * @deprecated 102.0.0
     */
    public function get_data($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }
    /**
     * @return int
     *
     * @deprecated 102.0.0
     */
    public function get_level()
    {
        return $this->level;
    }
    /**
     * @return int
     *
     * @deprecated 102.0.0
     */
    public function get_left()
    {
        return $this->left;
    }
    /**
     * @return int
     *
     * @deprecated 102.0.0
     */
    public function get_right()
    {
        return $this->right;
    }
    /**
     * @return string|int
     *
     * @deprecated 102.0.0
     */
    public function get_pid()
    {
        return $this->pid;
    }
    /**
     * @return string|int
     *
     * @deprecated 102.0.0
     */
    public function get_id()
    {
        return $this->id;
    }
    /**
     * Return true if node has child
     *
     * @return bool
     *
     * @deprecated 102.0.0
     */
    public function is_parent()
    {
        if ($this->right - $this->left > 1) {
            return true;
        } else {
            return false;
        }
    }
}