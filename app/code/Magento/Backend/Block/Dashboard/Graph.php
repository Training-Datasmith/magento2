<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard google chart block
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.chart.amounts and dashboard.chart.orders in adminhtml_dashboard_index.xml
 */
class Graph extends \Magento\Backend\Block\Dashboard\Abstract_Dashboard
{
    public const API_URL = 'https://image-charts.com/chart';
    /**
     * @var array
     */
    protected $_all_series = [];
    /**
     * @var array
     */
    protected $_axis_labels = [];
    /**
     * @var array
     */
    protected $_axis_maps = [];
    /**
     * @var array
     */
    protected $_data_rows = [];
    /**
     * Simple encoding chars
     *
     * @var string
     */
    protected $_simple_encoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    /**
     * Extended encoding chars
     *
     * @var string
     */
    protected $_extended_encoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
    /**
     * Chart width
     *
     * @var string
     */
    protected $_width = '780';
    /**
     * Chart height
     *
     * @var string
     */
    protected $_height = '384';
    /**
     * Google chart api data encoding
     *
     * @deprecated 101.0.2 since the Google Image Charts API not accessible from March 14, 2019
     * @see Nothing
     * @var string
     */
    protected $_encoding = 'e';
    /**
     * Html identifier
     *
     * @var string
     */
    protected $_html_id = '';
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/graph.phtml';
    /**
     * Adminhtml dashboard data
     *
     * @var \Magento\Backend\Helper\Dashboard\Data
     */
    protected $_dashboard_data = null;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Reports\Model\Resource_Model\Order\Collection_Factory $collection_factory, \Magento\Backend\Helper\Dashboard\Data $dashboard_data, array $data = [])
    {
        parent::__construct($context, $collection_factory, $data);
        $this->_dashboard_data = $dashboard_data;
    }
    /**
     * Get tab template
     *
     * @return string
     */
    protected function _get_tab_template()
    {
        return 'dashboard/graph.phtml';
    }
    /**
     * Set data rows
     *
     * @param string $rows
     * @return void
     */
    public function set_data_rows($rows)
    {
        $this->_data_rows = (array) $rows;
    }
    /**
     * Add series
     *
     * @param string $seriesId
     * @param array $options
     * @return void
     */
    public function add_series($series_id, array $options)
    {
        $this->_all_series[$series_id] = $options;
    }
    /**
     * Get series
     *
     * @param string $seriesId
     * @return array|bool
     */
    public function get_series($series_id)
    {
        if (isset($this->_all_series[$series_id])) {
            return $this->_all_series[$series_id];
        }
        return false;
    }
    /**
     * Get all series
     *
     * @return array
     */
    public function get_all_series()
    {
        return $this->_all_series;
    }
    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_chart_url($direct_url = true)
    {
        $params = ['cht' => 'lc', 'chls' => '7', 'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0', 'chm' => 'B,f4d4b2,0,0,0', 'chco' => 'db4814', 'chxs' => '0,0,11|1,0,11', 'chma' => '15,15,15,15'];
        $this->_all_series = $this->get_rows_data($this->_data_rows);
        foreach ($this->_axis_maps as $axis => $attr) {
            $this->set_axis_labels($axis, $this->get_rows_data($attr, true));
        }
        $timezone_local = $this->_locale_date->get_config_timezone();
        /** @var \DateTime $dateStart */
        /** @var \DateTime $dateEnd */
        list($date_start, $date_end) = $this->_collection_factory->create()->get_date_range($this->get_data_helper()->get_param('period'), '', '', true);
        $date_start->set_timezone(new \DateTimeZone($timezone_local));
        $date_end->set_timezone(new \DateTimeZone($timezone_local));
        if ($this->get_data_helper()->get_param('period') == '24h') {
            $date_end->modify('-1 hour');
        } else {
            $date_end->set_time(23, 59, 59);
            $date_start->set_time(0, 0, 0);
        }
        $dates = [];
        $datas = [];
        while ($date_start <= $date_end) {
            switch ($this->get_data_helper()->get_param('period')) {
                case '7d':
                case '1m':
                    $d = $date_start->format('Y-m-d');
                    $date_start->modify('+1 day');
                    break;
                case '1y':
                case '2y':
                    $d = $date_start->format('Y-m');
                    $date_start->modify('first day of next month');
                    break;
                default:
                    $d = $date_start->format('Y-m-d H:00');
                    $date_start->modify('+1 hour');
            }
            foreach ($this->get_all_series() as $index => $serie) {
                if (in_array($d, $this->_axis_labels['x'])) {
                    $datas[$index][] = (float) array_shift($this->_all_series[$index]);
                } else {
                    $datas[$index][] = 0;
                }
            }
            $dates[] = $d;
        }
        /**
         * setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        } else if (count($dates) >= 15) {
            $c = 2;
        } else {
            $c = 0;
        }
        /**
         * skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i = 0;
            } else {
                $dates[$k] = '';
                $i++;
            }
        }
        $this->_axis_labels['x'] = $dates;
        $this->_all_series = $datas;
        // Image-Charts Awesome data format values
        $params['chd'] = 'a:';
        $data_delimiter = ',';
        $data_setdelimiter = '|';
        $data_missing = '_';
        // process each string in the array, and find the max length
        $localmaxvalue = [0];
        $localminvalue = [0];
        foreach ($this->get_all_series() as $index => $serie) {
            $localmaxvalue[$index] = max($serie);
            $localminvalue[$index] = min($serie);
        }
        $maxvalue = max($localmaxvalue);
        $minvalue = min($localminvalue);
        // default values
        $miny = 0;
        $maxy = 0;
        $yorigin = 0;
        $x_axis = 'x';
        $x_axis_index = 0;
        $y_axis_index = 1;
        if ($minvalue >= 0 && $maxvalue >= 0) {
            if ($maxvalue > 10) {
                $p = pow(10, $this->_get_pow((int) $maxvalue));
                $maxy = ceil($maxvalue / $p) * $p;
                $y_range = "{$y_axis_index},{$miny},{$maxy},{$p}";
            } else {
                $maxy = ceil($maxvalue + 1);
                $y_range = "{$y_axis_index},{$miny},{$maxy},1";
            }
            $params['chxr'] = $y_range;
            $yorigin = 0;
        }
        $chartdata = [];
        foreach ($this->get_all_series() as $index => $serie) {
            $thisdataarray = $serie;
            $count = count($thisdataarray);
            for ($j = 0; $j < $count; $j++) {
                $currentvalue = $thisdataarray[$j];
                if (is_numeric($currentvalue)) {
                    $ylocation = $yorigin + $currentvalue;
                    $chartdata[] = $ylocation . $data_delimiter;
                } else {
                    $chartdata[] = $data_missing . $data_delimiter;
                }
            }
            $chartdata[] = $data_setdelimiter;
        }
        $buffer = implode('', $chartdata);
        $buffer = rtrim($buffer, $data_setdelimiter);
        $buffer = rtrim($buffer, $data_delimiter);
        $buffer = str_replace($data_delimiter . $data_setdelimiter, $data_setdelimiter, $buffer);
        $params['chd'] .= $buffer;
        if (count($this->_axis_labels) > 0) {
            $params['chxt'] = implode(',', array_keys($this->_axis_labels));
            $this->format_axis_label_date($x_axis, (string) $timezone_local);
            $custom_axis_labels = $x_axis_index . ':|' . implode('|', $this->_axis_labels[$x_axis]);
            $params['chxl'] = $custom_axis_labels . $data_setdelimiter;
        }
        // chart size
        $params['chs'] = $this->get_width() . 'x' . $this->get_height();
        // return the encoded data
        if ($direct_url) {
            $p = [];
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return (string) self::API_URL . '?' . implode('&', $p);
        }
        $ga_data = urlencode(base64_encode(json_encode($params)));
        $ga_hash = $this->_dashboard_data->get_chart_data_hash($ga_data);
        $params = ['ga' => $ga_data, 'h' => $ga_hash];
        return $this->get_url('adminhtml/*/tunnel', ['_query' => $params]);
    }
    /**
     * Format dates for axis labels
     *
     * @param string $idx
     * @param string $timezoneLocal
     *
     * @return void
     */
    private function format_axis_label_date($idx, $timezone_local)
    {
        foreach ($this->_axis_labels[$idx] as $_index => $_label) {
            if ($_label != '') {
                $period = new \DateTime($_label, new \DateTimeZone($timezone_local));
                switch ($this->get_data_helper()->get_param('period')) {
                    case '24h':
                        $this->_axis_labels[$idx][$_index] = $this->_locale_date->format_date_time($period->set_time((int) $period->format('H'), 0, 0), \Intl_Date_Formatter::NONE, \Intl_Date_Formatter::SHORT);
                        break;
                    case '7d':
                    case '1m':
                        $this->_axis_labels[$idx][$_index] = $this->_locale_date->format_date_time($period, \Intl_Date_Formatter::SHORT, \Intl_Date_Formatter::NONE);
                        break;
                    case '1y':
                    case '2y':
                        $this->_axis_labels[$idx][$_index] = date('m/Y', strtotime($_label));
                        break;
                }
            } else {
                $this->_axis_labels[$idx][$_index] = '';
            }
        }
    }
    /**
     * Get rows data
     *
     * @param array $attributes
     * @param bool $single
     * @return array
     */
    protected function get_rows_data($attributes, $single = false)
    {
        $items = $this->get_collection()->get_items();
        $options = [];
        foreach ($items as $item) {
            if ($single) {
                $options[] = max(0, $item->get_data($attributes));
            } else {
                foreach ((array) $attributes as $attr) {
                    $options[$attr][] = max(0, $item->get_data($attr));
                }
            }
        }
        return $options;
    }
    /**
     * Set axis labels
     *
     * @param string $axis
     * @param array $labels
     * @return void
     */
    public function set_axis_labels($axis, $labels)
    {
        $this->_axis_labels[$axis] = $labels;
    }
    /**
     * Set html id
     *
     * @param string $htmlId
     * @return void
     */
    public function set_html_id($html_id)
    {
        $this->_html_id = $html_id;
    }
    /**
     * Get html id
     *
     * @return string
     */
    public function get_html_id()
    {
        return $this->_html_id;
    }
    /**
     * Return pow
     *
     * @param int $number
     * @return int
     */
    protected function _get_pow($number)
    {
        $pow = 0;
        while ($number >= 10) {
            $number = $number / 10;
            $pow++;
        }
        return $pow;
    }
    /**
     * Return chart width
     *
     * @return string
     */
    protected function get_width()
    {
        return $this->_width;
    }
    /**
     * Return chart height
     *
     * @return string
     */
    protected function get_height()
    {
        return $this->_height;
    }
    /**
     * Sets data helper
     *
     * @param \Magento\Backend\Helper\Dashboard\AbstractDashboard $dataHelper
     * @return void
     */
    public function set_data_helper(\Magento\Backend\Helper\Dashboard\Abstract_Dashboard $data_helper)
    {
        $this->_data_helper = $data_helper;
    }
    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepare_data()
    {
        if ($this->_data_helper !== null) {
            $available_periods = array_keys($this->_dashboard_data->get_date_periods());
            $period = $this->get_request()->get_param('period');
            $this->get_data_helper()->set_param('period', $period && in_array($period, $available_periods) ? $period : '24h');
        }
    }
}