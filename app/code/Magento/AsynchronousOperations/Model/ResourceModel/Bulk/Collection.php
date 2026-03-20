<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model\Bulk;

/**
 * Class Collection
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
        $this->_init(\Magento\Asynchronous_Operations\Model\Bulk_Summary::class, \Magento\Asynchronous_Operations\Model\Resource_Model\Bulk::class);
        $this->set_main_table('magento_bulk');
    }
}