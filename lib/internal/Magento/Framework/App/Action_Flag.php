<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Request processing flag that allows to stop request dispatching in action controller from an observer
 * Downside of this approach is temporal coupling and global communication.
 * Will be deprecated when Action Component is decoupled.
 *
 * Please use plugins to prevent action dispatching instead.
 *
 * @api
 * @since 100.0.2
 */
class Action_Flag implements Reset_After_Request_Interface
{
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var array
     */
    protected $_flags = [];
    /**
     * @param RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\Request_Interface $request)
    {
        $this->_request = $request;
    }
    /**
     * Setting flag value
     *
     * @param string $action
     * @param string $flag
     * @param string $value
     * @return void
     */
    public function set($action, $flag, $value)
    {
        if ('' === $action) {
            $action = $this->_request->get_action_name();
        }
        $action_key = $action ?? '';
        $flag_key = $flag ?? '';
        $this->_flags[$this->_get_controller_key()][$action_key][$flag_key] = $value;
    }
    /**
     * Retrieve flag value
     *
     * @param   string $action
     * @param   string $flag
     * @return  bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get($action, $flag = '')
    {
        if ('' === $action) {
            $action = $this->_request->get_action_name();
        }
        if ('' === $flag) {
            return $this->_flags[$this->_get_controller_key()] ?? [];
        } elseif (isset($this->_flags[$this->_get_controller_key()][$action][$flag])) {
            return $this->_flags[$this->_get_controller_key()][$action][$flag];
        } else {
            return false;
        }
    }
    /**
     * Get controller key
     *
     * @return string
     */
    protected function _get_controller_key()
    {
        return $this->_request->get_route_name() . '_' . $this->_request->get_controller_name();
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->_flags = [];
    }
}