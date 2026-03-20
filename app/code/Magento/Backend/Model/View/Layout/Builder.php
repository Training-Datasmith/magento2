<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Layout;

/**
 * @api
 * @since 100.0.2
 */
class Builder extends \Magento\Framework\View\Layout\Builder
{
    /**
     * @return $this
     */
    protected function after_generate_block()
    {
        $this->layout->init_messages();
        return $this;
    }
}