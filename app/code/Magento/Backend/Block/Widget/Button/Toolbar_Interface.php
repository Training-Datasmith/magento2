<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ToolbarInterface
 *
 * @api
 */
interface Toolbar_Interface
{
    /**
     * Push buttons into toolbar
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return void
     */
    public function push_buttons(\Magento\Framework\View\Element\Abstract_Block $context, \Magento\Backend\Block\Widget\Button\Button_List $button_list);
}