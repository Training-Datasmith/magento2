<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * @api
 * @since 100.2.0
 */
interface Visibility_Checker_Interface extends Argument_Interface
{
    /**
     * Check that action can be displayed on massaction list
     *
     * @return bool
     * @since 100.2.0
     */
    public function is_visible();
}