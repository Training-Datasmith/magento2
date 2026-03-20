<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Command to update menu item data
 * @api
 * @since 100.0.2
 */
class Update extends \Magento\Backend\Model\Menu\Builder\Abstract_Command
{
    /**
     * Update item data
     *
     * @param array $itemParams
     * @return array
     */
    protected function _execute(array $item_params)
    {
        foreach ($this->_data as $key => $value) {
            $item_params[$key] = $value;
        }
        return $item_params;
    }
}