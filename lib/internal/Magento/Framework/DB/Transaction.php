<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB;

/**
 * DB transaction model
 *
 * @api
 *
 * @todo need collect connection by name
 */
class Transaction
{
    /**
     * Objects which will be involved to transaction
     *
     * @var array
     */
    protected $_objects = [];
    /**
     * Transaction objects array with alias key
     *
     * @var array
     */
    protected $_objects_by_alias = [];
    /**
     * Callbacks array.
     *
     * @var array
     */
    protected $_before_commit_callbacks = [];
    /**
     * Begin transaction for all involved object resources
     *
     * @return $this
     */
    protected function _start_transaction()
    {
        foreach ($this->_objects as $object) {
            $object->get_resource()->begin_transaction();
        }
        return $this;
    }
    /**
     * Commit transaction for all resources
     *
     * @return $this
     */
    protected function _commit_transaction()
    {
        foreach ($this->_objects as $object) {
            $object->get_resource()->commit();
        }
        return $this;
    }
    /**
     * Rollback transaction
     *
     * @return $this
     */
    protected function _rollback_transaction()
    {
        foreach ($this->_objects as $object) {
            $object->get_resource()->roll_back();
        }
        return $this;
    }
    /**
     * Run all configured object callbacks
     *
     * @return $this
     */
    protected function _run_callbacks()
    {
        foreach ($this->_before_commit_callbacks as $callback) {
            call_user_func($callback);
        }
        return $this;
    }
    /**
     * Adding object for using in transaction
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $alias
     * @return $this
     */
    public function add_object(\Magento\Framework\Model\Abstract_Model $object, $alias = '')
    {
        $this->_objects[] = $object;
        if (!empty($alias)) {
            $this->_objects_by_alias[$alias] = $object;
        }
        return $this;
    }
    /**
     * Add callback function which will be called before commit transactions
     *
     * @param callable $callback
     * @return $this
     */
    public function add_commit_callback($callback)
    {
        $this->_before_commit_callbacks[] = $callback;
        return $this;
    }
    /**
     * Initialize objects save transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $this->_start_transaction();
        $error = false;
        try {
            foreach ($this->_objects as $object) {
                $object->save();
            }
        } catch (\Exception $e) {
            $error = $e;
        }
        if ($error === false) {
            try {
                $this->_run_callbacks();
            } catch (\Exception $e) {
                $error = $e;
            }
        }
        if ($error) {
            $this->_rollback_transaction();
            throw $error;
        } else {
            $this->_commit_transaction();
        }
        return $this;
    }
    /**
     * Initialize objects delete transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        $this->_start_transaction();
        $error = false;
        try {
            foreach ($this->_objects as $object) {
                $object->delete();
            }
        } catch (\Exception $e) {
            $error = $e;
        }
        if ($error === false) {
            try {
                $this->_run_callbacks();
            } catch (\Exception $e) {
                $error = $e;
            }
        }
        if ($error) {
            $this->_rollback_transaction();
            throw $error;
        } else {
            $this->_commit_transaction();
        }
        return $this;
    }
}