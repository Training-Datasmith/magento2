<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Zend_Db_Expr;
class Option_Qty_Expression_Provider
{
    /**
     * Get expression for calculating available quantity for bundle option.
     *
     * @return Zend_Db_Expr
     */
    public function get_expression(): Zend_Db_Expr
    {
        return new Zend_Db_Expr('i.qty - cisi.min_qty');
    }
}