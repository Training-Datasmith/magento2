<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder;

/**
 * Menu builder command
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Command
{
    /**
     * List of required params
     *
     * @var string[]
     */
    protected $_required_params = ['id'];
    /**
     * Command params array
     *
     * @var array
     */
    protected $_data = [];
    /**
     * Next command in the chain
     *
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected $_next = null;
    /**
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        foreach ($this->_required_params as $param) {
            if (!isset($data[$param]) || $data[$param] === null) {
                throw new \InvalidArgumentException('Missing required param ' . $param);
            }
        }
        $this->_data = $data;
    }
    /**
     * Retrieve id of element to apply command to
     *
     * @return int
     */
    public function get_id()
    {
        return $this->_data['id'];
    }
    /**
     * Add command as last in the list of callbacks
     *
     * @param \Magento\Backend\Model\Menu\Builder\AbstractCommand $command
     * @return $this
     * @throws \InvalidArgumentException if invalid chaining command is supplied
     */
    public function chain(\Magento\Backend\Model\Menu\Builder\Abstract_Command $command)
    {
        if ($this->_next === null) {
            $this->_next = $command;
        } else {
            $this->_next->chain($command);
        }
        return $this;
    }
    /**
     * Execute command and pass control to chained commands
     *
     * @param array $itemParams
     * @return array
     */
    public function execute(array $item_params = [])
    {
        $item_params = $this->_execute($item_params);
        if ($this->_next !== null) {
            $item_params = $this->_next->execute($item_params);
        }
        return $item_params;
    }
    /**
     * Execute internal command actions
     *
     * @param array $itemParams
     * @return array
     */
    abstract protected function _execute(array $item_params);
}