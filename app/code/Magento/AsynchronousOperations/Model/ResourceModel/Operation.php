<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model;

use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
/**
 * Resource class for Bulk Operations
 */
class Operation extends Abstract_Db
{
    public const TABLE_NAME = 'magento_operation';
    public const TABLE_PRIMARY_KEY = 'id';
    /**
     * Initialize banner sales rule resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::TABLE_PRIMARY_KEY);
    }
}