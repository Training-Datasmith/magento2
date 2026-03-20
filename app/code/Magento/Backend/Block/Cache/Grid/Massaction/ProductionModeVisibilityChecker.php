<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Cache\Grid\Massaction;

use Magento\Backend\Block\Widget\Grid\Massaction\Visibility_Checker_Interface;
use Magento\Framework\App\State;
/**
 * Class checks that action can be displayed on massaction list
 */
class Production_Mode_Visibility_Checker implements Visibility_Checker_Interface
{
    /**
     * @var State
     */
    private $state;
    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }
    /**
     * {@inheritdoc}
     */
    public function is_visible()
    {
        return $this->state->get_mode() !== State::MODE_PRODUCTION;
    }
}