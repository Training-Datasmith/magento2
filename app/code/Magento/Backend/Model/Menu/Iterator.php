<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

/**
 * Menu iterator
 * @api
 * @since 100.0.2
 */
class Iterator extends \ArrayIterator
{
    /**
     * Rewind to first element
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        $this->ksort();
        parent::rewind();
    }
}