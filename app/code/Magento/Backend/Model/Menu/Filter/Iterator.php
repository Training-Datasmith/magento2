<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Filter;

/**
 * Menu filter iterator
 * @api
 * @since 100.0.2
 */
class Iterator extends \Filter_Iterator
{
    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    #[\Return_Type_Will_Change]
    public function accept()
    {
        return !($this->current()->is_disabled() || !$this->current()->is_allowed());
    }
}