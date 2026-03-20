<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model\Operation;

use Magento\Asynchronous_Operations\Model\Operation;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation as OperationResourceModel;
/**
 * Class Collection for Magento Operation table
 * @codeCoverageIgnore
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * Define collection item type and corresponding table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Operation::class, Operation_Resource_Model::class);
        $this->set_main_table('magento_operation');
        $this->_set_id_field_name(Operation_Resource_Model::TABLE_PRIMARY_KEY);
    }
}