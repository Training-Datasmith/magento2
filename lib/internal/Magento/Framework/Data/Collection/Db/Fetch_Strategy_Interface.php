<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data\Collection\Db;

use Magento\Framework\DB\Select;
/**
 * Interface \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
 *
 * @api
 */
interface Fetch_Strategy_Interface
{
    /**
     * Retrieve all records
     *
     * @param Select $select
     * @param array $bindParams
     * @return array
     */
    public function fetch_all(Select $select, array $bind_params = []);
}