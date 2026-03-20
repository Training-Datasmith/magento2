<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model;

/**
 * Class Bulk
 */
class Bulk extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * Initialize banner sales rule resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_bulk', 'uuid');
    }
}