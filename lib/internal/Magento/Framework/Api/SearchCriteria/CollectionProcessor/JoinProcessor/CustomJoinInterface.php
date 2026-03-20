<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor\Join_Processor;

use Magento\Framework\Data\Collection\Abstract_Db;
/**
 * @api
 * @since 101.0.0
 */
interface Custom_Join_Interface
{
    /**
     * Make custom joins to collection
     *
     * @param AbstractDb $collection
     * @return bool
     * @since 101.0.0
     */
    public function apply(Abstract_Db $collection);
}