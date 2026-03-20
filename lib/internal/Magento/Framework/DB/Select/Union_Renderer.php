<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
/**
 * Class UnionRenderer
 */
class Union_Renderer implements Renderer_Interface
{
    /**
     * Render UNION section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->get_part(Select::UNION)) {
            $sql = '';
            $parts = count($select->get_part(Select::UNION));
            foreach ($select->get_part(Select::UNION) as $cnt => $union) {
                list($target, $type) = $union;
                if ($target instanceof Select) {
                    $target = $target->assemble();
                }
                $sql .= $target;
                if ($cnt < $parts - 1) {
                    $sql .= ' ' . $type . ' ';
                }
            }
        }
        return $sql;
    }
}