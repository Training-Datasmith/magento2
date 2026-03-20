<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Stdlib\DateTime\Date_Time_Formatter_Interface;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Date grid column filter
 * @api
 * @since 100.0.2
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $math_random;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $locale_resolver;
    /**
     * @var DateTimeFormatterInterface
     */
    protected $date_time_formatter;
    /**
     * @var SecureHtmlRenderer
     */
    protected $secure_html_renderer;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resource_helper, \Magento\Framework\Math\Random $math_random, \Magento\Framework\Locale\Resolver_Interface $locale_resolver, Date_Time_Formatter_Interface $date_time_formatter, array $data = [], ?Secure_Html_Renderer $secure_html_renderer = null)
    {
        $this->math_random = $math_random;
        $this->locale_resolver = $locale_resolver;
        parent::__construct($context, $resource_helper, $data);
        $this->date_time_formatter = $date_time_formatter;
        $this->secure_html_renderer = $secure_html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * @inheritDoc
     */
    public function get_html()
    {
        $html_id = $this->math_random->get_unique_hash($this->_get_html_id());
        $format = $this->_locale_date->get_date_format(\Intl_Date_Formatter::SHORT);
        $html = '<div class="range" id="' . $html_id . '_range"><div class="range-line date">' . '<input type="text" name="' . $this->_get_html_name() . '[from]" id="' . $html_id . '_from"' . ' value="' . $this->get_escaped_value('from') . '" class="admin__control-text input-text no-changes" placeholder="' . __('From') . '" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'from') . '/>' . '</div>';
        $html .= '<div class="range-line date">' . '<input type="text" name="' . $this->_get_html_name() . '[to]" id="' . $html_id . '_to"' . ' value="' . $this->get_escaped_value('to') . '" class="input-text admin__control-text no-changes" placeholder="' . __('To') . '" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'to') . '/>' . '</div></div>';
        $html .= '<input type="hidden" name="' . $this->_get_html_name() . '[locale]"' . ' value="' . $this->locale_resolver->get_locale() . '"/>';
        $script_string = 'require(["jquery", "mage/calendar"], function($){
                $("#' . $html_id . '_range").dateRange({
                    dateFormat: "' . $format . '",
                    buttonText: "' . $this->escape_html(__('Date selector')) . '",
                    buttonImage: "' . $this->get_view_file_url('Magento_Theme::calendar.png') . '",
                    from: {
                        id: "' . $html_id . '_from"
                    },
                    to: {
                        id: "' . $html_id . '_to"
                    }
                })
            });';
        $html .= $this->secure_html_renderer->render_tag('script', [], $script_string, false);
        return $html;
    }
    /**
     * Return escaped value.
     *
     * @param string|null $index
     * @return array|string|int|float|null
     */
    public function get_escaped_value($index = null)
    {
        $value = $this->get_value($index);
        if ($value instanceof \DateTimeInterface) {
            return $this->date_time_formatter->format_object($value, $this->_locale_date->get_date_format(\Intl_Date_Formatter::SHORT));
        }
        if (is_string($value)) {
            return $this->escape_html($value);
        }
        return $value;
    }
    /**
     * Return value.
     *
     * @param string|null $index
     * @return array|string|int|float|null
     */
    public function get_value($index = null)
    {
        if ($index) {
            if ($data = $this->get_data('value', 'orig_' . $index)) {
                return $data;
            }
            return null;
        }
        $value = $this->get_data('value');
        if (is_array($value)) {
            $value['date'] = true;
        }
        return $value;
    }
    /**
     * Return conditions.
     *
     * @return array|string|int|float|null
     */
    public function get_condition()
    {
        $value = $this->get_value();
        return $value;
    }
    /**
     * Set value.
     *
     * @param array|string|int|float $value
     * @return $this
     */
    public function set_value($value)
    {
        if (isset($value['locale'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->_convert_date($value['from']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->_convert_date($value['to']);
            }
        }
        if (empty($value['from']) && empty($value['to'])) {
            $value = null;
        }
        $this->set_data('value', $value);
        return $this;
    }
    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @return \DateTime|null
     */
    protected function _convert_date($date)
    {
        $timezone = $this->get_column()->get_timezone() !== false ? $this->_locale_date->get_config_timezone() : 'UTC';
        $admin_time_zone = new \DateTimeZone($timezone);
        $formatter = new \Intl_Date_Formatter($this->locale_resolver->get_locale(), \Intl_Date_Formatter::SHORT, \Intl_Date_Formatter::NONE, $admin_time_zone);
        $simple_res = new \DateTime('now', $admin_time_zone);
        $simple_res->set_timestamp($formatter->parse($date));
        $simple_res->set_time(0, 0, 0);
        $simple_res->set_timezone(new \DateTimeZone('UTC'));
        return $simple_res;
    }
}