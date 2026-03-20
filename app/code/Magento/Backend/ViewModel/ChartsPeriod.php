<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\View_Model;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * View model for dashboard charts period select
 */
class Charts_Period implements Argument_Interface
{
    /**
     * @var Period
     */
    private $period;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @param Period $period
     * @param Json $serializer
     */
    public function __construct(Period $period, Json $serializer)
    {
        $this->period = $period;
        $this->serializer = $serializer;
    }
    /**
     * Get chart date periods
     *
     * @return array
     */
    public function get_date_periods(): array
    {
        return $this->period->get_date_periods();
    }
    /**
     * Get json-encoded chart period units
     *
     * @return string
     */
    public function get_period_units(): string
    {
        return $this->serializer->serialize($this->period->get_period_chart_units());
    }
}