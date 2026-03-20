<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Limit_Expression;
/**
 * Class LimitRenderer
 */
class Limit_Renderer implements Renderer_Interface
{
    /**
     * Render LIMIT section
     *
     * @param Select $select
     * @param string $sql
     * @return LimitExpression|string
     */
    public function render(Select $select, $sql = '')
    {
        $count = 0;
        $offset = 0;
        if (!empty($select->get_part(Select::LIMIT_OFFSET))) {
            $offset = (int) $select->get_part(Select::LIMIT_OFFSET);
            $count = PHP_INT_MAX;
        }
        if (!empty($select->get_part(Select::LIMIT_COUNT))) {
            $count = (int) $select->get_part(Select::LIMIT_COUNT);
        }
        /*
         * Add limits clause
         */
        if ($count > 0) {
            $sql = new Limit_Expression($sql, $count, $offset);
        }
        return $sql;
    }
}