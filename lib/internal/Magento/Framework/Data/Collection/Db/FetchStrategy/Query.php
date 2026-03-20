<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Retrieving collection data by querying a database
 */
namespace Magento\Framework\Data\Collection\Db\Fetch_Strategy;

use Magento\Framework\DB\Select;
class Query implements \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface
{
    /**
     * {@inheritdoc}
     */
    public function fetch_all(Select $select, array $bind_params = [])
    {
        return $select->get_connection()->fetch_all($select, $bind_params);
    }
}