<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart\Date;
/**
 * Dashboard chart data retriever
 */
class Chart
{
    /**
     * @var Date
     */
    private $date_retriever;
    /**
     * @var OrderHelper
     */
    private $order_helper;
    /**
     * @var Period
     */
    private $period;
    /**
     * Chart constructor.
     * @param Date $dateRetriever
     * @param OrderHelper $orderHelper
     * @param Period $period
     */
    public function __construct(Date $date_retriever, Order_Helper $order_helper, Period $period)
    {
        $this->date_retriever = $date_retriever;
        $this->order_helper = $order_helper;
        $this->period = $period;
    }
    /**
     * Get chart data by period and chart type parameter, with possibility to pass scope parameters
     *
     * @param string $period
     * @param string $chartParam
     * @param string|null $store
     * @param string|null $website
     * @param string|null $group
     *
     * @return array
     */
    public function get_by_period(string $period, string $chart_param, ?string $store = null, ?string $website = null, ?string $group = null): array
    {
        $this->order_helper->set_param('store', $store);
        $this->order_helper->set_param('website', $website);
        $this->order_helper->set_param('group', $group);
        $available_periods = array_keys($this->period->get_date_periods());
        $this->order_helper->set_param('period', $period && in_array($period, $available_periods, false) ? $period : Period::PERIOD_24_HOURS);
        $dates = $this->date_retriever->get_by_period($period);
        $collection = $this->order_helper->get_collection();
        $data = [];
        if ($collection->count() > 0) {
            foreach ($dates as $date) {
                $item = $collection->get_item_by_column_value('range', $date);
                $data[] = ['x' => $date, 'y' => $item ? (float) $item->get_data($chart_param) : 0];
            }
        }
        return $data;
    }
}