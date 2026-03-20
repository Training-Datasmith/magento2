<?php

declare (strict_types=1);
/**
 * Application config file resolver
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Arguments;

class Validation_State implements \Magento\Framework\Config\Validation_State_Interface
{
    /**
     * @var string
     */
    protected $_app_mode;
    /**
     * @param string $appMode
     */
    public function __construct($app_mode)
    {
        $this->_app_mode = $app_mode;
    }
    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function is_validation_required()
    {
        return $this->_app_mode == \Magento\Framework\App\State::MODE_DEVELOPER;
    }
}