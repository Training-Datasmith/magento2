<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Widget\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Sub_Totals extends \Magento\Backend\Model\Widget\Grid\Abstract_Totals
{
    /**
     * Count collection column sum based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _count_sum($index, $collection)
    {
        $sum = 0;
        foreach ($collection as $item) {
            $sum += $item[$index];
        }
        return $sum;
    }
    /**
     * Count collection column average based on column index
     *
     * @param string $index
     * @param \Magento\Framework\Data\Collection $collection
     * @return float|int
     */
    protected function _count_average($index, $collection)
    {
        $items_count = count($collection);
        return $items_count ? $this->_count_sum($index, $collection) / $items_count : $items_count;
    }
}