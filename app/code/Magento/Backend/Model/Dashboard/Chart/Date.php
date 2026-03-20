<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Dashboard\Chart;

use DateTimeZone;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\Stdlib\DateTime\Timezone_Interface;
use Magento\Reports\Model\Resource_Model\Order\Collection_Factory;
/**
 * Dashboard chart dates retriever
 */
class Date
{
    /**
     * @var CollectionFactory
     */
    private $collection_factory;
    /**
     * @var TimezoneInterface
     */
    private $locale_date;
    /**
     * Date constructor.
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(Collection_Factory $collection_factory, Timezone_Interface $locale_date)
    {
        $this->collection_factory = $collection_factory;
        $this->locale_date = $locale_date;
    }
    /**
     * Get chart dates data by period
     *
     * @param string $period
     *
     * @return array
     */
    public function get_by_period(string $period): array
    {
        [$date_start, $date_end] = $this->collection_factory->create()->get_date_range($period, '', '', true);
        $timezone_local = $this->locale_date->get_config_timezone();
        $date_start->set_timezone(new DateTimeZone($timezone_local));
        $date_end->set_timezone(new DateTimeZone($timezone_local));
        if ($period === Period::PERIOD_24_HOURS) {
            $date_end->modify('-1 hour');
        }
        $dates = [];
        while ($date_start <= $date_end) {
            switch ($period) {
                case Period::PERIOD_7_DAYS:
                case Period::PERIOD_1_MONTH:
                    $d = $date_start->format('Y-m-d');
                    $date_start->modify('+1 day');
                    break;
                case Period::PERIOD_1_YEAR:
                case Period::PERIOD_2_YEARS:
                    $d = $date_start->format('Y-m');
                    $date_start->modify('first day of next month');
                    break;
                default:
                    $d = $date_start->format('Y-m-d H:00');
                    $date_start->modify('+1 hour');
            }
            $dates[] = $d;
        }
        return $dates;
    }
}