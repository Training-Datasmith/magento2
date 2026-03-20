<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

/**
 * Command to remove menu item
 * @api
 * @since 100.0.2
 */
class Remove extends \Magento\Backend\Model\Menu\Builder\Abstract_Command
{
    /**
     * Mark item as removed
     *
     * @param array $itemParams
     * @return array
     */
    protected function _execute(array $item_params)
    {
        $item_params['id'] = $this->get_id();
        $item_params['removed'] = true;
        return $item_params;
    }
}