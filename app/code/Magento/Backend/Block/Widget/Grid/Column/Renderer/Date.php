<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Stdlib\DateTime\Date_Time_Formatter_Interface;
/**
 * Backend grid item renderer date
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var int
     */
    protected $_default_width = 160;
    /**
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;
    /**
     * @var DateTimeFormatterInterface
     */
    protected $date_time_formatter;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, Date_Time_Formatter_Interface $date_time_formatter, array $data = [])
    {
        parent::__construct($context, $data);
        $this->date_time_formatter = $date_time_formatter;
    }
    /**
     * Retrieve date format
     *
     * @return string
     * @deprecated 100.1.0
     */
    protected function _get_format()
    {
        $format = $this->get_column()->get_format();
        if ($format === null) {
            if (self::$_format === null) {
                try {
                    self::$_format = $this->_locale_date->get_date_format(\Intl_Date_Formatter::MEDIUM);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
            $format = self::$_format;
        }
        return $format;
    }
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $format = $this->get_column()->get_format();
        $date = $this->_get_value($row);
        if ($date) {
            if (!$date instanceof \DateTimeInterface) {
                $date = new \DateTime($date);
            }
            return $this->_locale_date->format_date_time($date, $format ?: \Intl_Date_Formatter::MEDIUM, \Intl_Date_Formatter::NONE, null, $this->get_column()->get_timezone() === false ? 'UTC' : null);
        }
        return $this->get_column()->get_default();
    }
}