<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * @api
 * @since 100.0.2
 */
class Url_Generator_Id implements \Magento\Backend\Model\Widget\Grid\Row\Generator_Interface
{
    /**
     * Create url for passed item using passed url model
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function get_url($item)
    {
        return $item->get_id();
    }
}