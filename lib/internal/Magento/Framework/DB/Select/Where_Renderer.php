<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
/**
 * Class WhereRenderer
 */
class Where_Renderer implements Renderer_Interface
{
    /**
     * Render WHERE section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->get_part(Select::FROM) && $select->get_part(Select::WHERE)) {
            $sql .= ' ' . Select::SQL_WHERE . ' ' . implode(' ', $select->get_part(Select::WHERE));
        }
        return $sql;
    }
}