<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Builder command to add menu items
 * @api
 * @since 100.0.2
 */
class Add extends \Magento\Backend\Model\Menu\Builder\Abstract_Command
{
    /**
     * List of params that command requires for execution
     *
     * @var string[]
     */
    protected $_required_params = ['id', 'title', 'module', 'resource'];
    /**
     * Add command as last in the list of callbacks
     *
     * @param \Magento\Backend\Model\Menu\Builder\AbstractCommand $command
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function chain(\Magento\Backend\Model\Menu\Builder\Abstract_Command $command)
    {
        if ($command instanceof \Magento\Backend\Model\Menu\Builder\Command\Add) {
            throw new \InvalidArgumentException("Two 'add' commands cannot have equal id (" . $command->get_id() . ')');
        }
        return parent::chain($command);
    }
    /**
     * Add missing data to item
     *
     * @param array $itemParams
     * @return array
     */
    protected function _execute(array $item_params)
    {
        foreach ($this->_data as $key => $value) {
            $item_params[$key] = isset($item_params[$key]) ? $item_params[$key] : $value;
        }
        return $item_params;
    }
}