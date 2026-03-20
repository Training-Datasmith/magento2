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
interface Generator_Interface
{
    /**
     * Generate row url
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function get_url($item);
}