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
 * Class GroupRenderer
 */
class Group_Renderer implements Renderer_Interface
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
     * Render GROUP BY section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->get_part(Select::FROM) && $select->get_part(Select::GROUP)) {
            $group = [];
            foreach ($select->get_part(Select::GROUP) as $term) {
                $group[] = $this->quote->quote_identifier($term);
            }
            $sql .= ' ' . Select::SQL_GROUP_BY . ' ' . implode(",\n\t", $group);
        }
        return $sql;
    }
}