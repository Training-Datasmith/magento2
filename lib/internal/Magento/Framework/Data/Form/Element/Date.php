<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Magento data selector form element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\Timezone_Interface;
/**
 * Date element
 */
class Date extends Abstract_Element
{
    /**
     * @var \DateTime
     */
    protected $_value;
    /**
     * @var TimezoneInterface
     */
    protected $locale_date;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param TimezoneInterface $localeDate
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, Timezone_Interface $locale_date, $data = [])
    {
        $this->locale_date = $locale_date;
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('text');
        $this->set_ext_type('textfield');
        if (isset($data['value'])) {
            $this->set_value($data['value']);
        }
    }
    /**
     * Check if a string is a date value
     *
     * @param string $value
     * @return bool
     */
    private function is_date(string $value): bool
    {
        $date = date_parse($value);
        return !empty($date['year']) && !empty($date['month']) && !empty($date['day']);
    }
    /**
     * Initial scope of method was to limit timestamp on x64 systems to mimic x32 systems,
     * but keeping the method for compatibility:
     * If script executes on x64 system, converts large numeric values to timestamp limit
     *
     * @param int $value
     * @return int
     */
    protected function _to_timestamp($value)
    {
        return $value;
    }
    /**
     * Set date value
     *
     * @param mixed $value
     * @return $this
     */
    public function set_value($value)
    {
        if (empty($value)) {
            $this->_value = '';
            return $this;
        }
        if ($value instanceof \DateTimeInterface) {
            $this->_value = $value;
            return $this;
        }
        try {
            if (preg_match('/^[\-]{0,1}[0-9]+$/', $value)) {
                $this->_value = (new \DateTime())->set_timestamp($this->_to_timestamp($value));
            } elseif (is_string($value) && $this->is_date($value)) {
                $this->_value = new \DateTime($value, new \DateTimeZone($this->locale_date->get_config_timezone()));
            } else {
                $this->_value = '';
            }
        } catch (\Exception $e) {
            $this->_value = '';
        }
        return $this;
    }
    /**
     * Get date value as string.
     *
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with \DateTime)
     * @return string
     */
    public function get_value($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->get_date_format() ?: $this->get_format();
            $format .= $format && $this->get_time_format() ? ' ' : '';
            $format .= $this->get_time_format() ? $this->get_time_format() : '';
        }
        return $this->locale_date->format_date_time($this->_value, null, null, null, $this->_value->get_timezone(), $format);
    }
    /**
     * Get value instance, if any
     *
     * @return \DateTime
     */
    public function get_value_instance()
    {
        if (empty($this->_value)) {
            return null;
        }
        return $this->_value;
    }
    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (\DateTime)
     * - output format must be set (compatible with \DateTime)
     *
     * @throws \Exception
     * @return string
     */
    public function get_element_html()
    {
        $this->add_class('admin__control-text input-text input-date');
        $date_format = $this->get_date_format() ?: $this->get_format();
        $time_format = $this->get_time_format();
        if (empty($date_format)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Output format is not specified. ' . 'Please specify "format" key in constructor, or set it using setFormat().');
        }
        $data_init = 'data-mage-init="' . $this->_escape(json_encode(['calendar' => ['dateFormat' => $date_format, 'showsTime' => !empty($time_format), 'timeFormat' => $time_format, 'buttonImage' => $this->get_image(), 'buttonText' => 'Select Date', 'disabled' => $this->get_disabled(), 'minDate' => $this->get_min_date(), 'maxDate' => $this->get_max_date()]])) . '"';
        $html = sprintf('<input name="%s" id="%s" value="%s" %s %s />', $this->get_name(), $this->get_html_id(), $this->_escape($this->get_value()), $this->serialize($this->get_html_attributes()), $data_init);
        $html .= $this->get_after_element_html();
        return $html;
    }
}