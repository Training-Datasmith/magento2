<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
/**
 * Class OrderRenderer
 */
class Order_Renderer implements Renderer_Interface
{
    /**
     * @var Quote
     */
    protected $quote;
    /**
     * @param Quote $quote
     */
    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }
    /**
     * Render ORDER BY section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->get_part(Select::ORDER)) {
            $order = [];
            foreach ($select->get_part(Select::ORDER) as $term) {
                if (is_array($term)) {
                    if (is_numeric($term[0]) && (string) (int) $term[0] == $term[0]) {
                        $order[] = (int) trim($term[0]) . ' ' . $term[1];
                    } else {
                        $order[] = $this->quote->quote_identifier($term[0]) . ' ' . $term[1];
                    }
                } elseif (is_numeric($term) && (string) (int) $term == $term) {
                    $order[] = (int) trim($term);
                } else {
                    $order[] = $this->quote->quote_identifier($term);
                }
            }
            $sql .= ' ' . Select::SQL_ORDER_BY . ' ' . implode(', ', $order) . PHP_EOL;
        }
        return $sql;
    }
}