<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Date grid column filter
 *
 * @todo        date format
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Date
{
    /**
     * full day is 86400, we need 23 hours:59 minutes:59 seconds = 86399
     */
    public const END_OF_DAY_IN_SECONDS = 86399;
    /**
     * @inheritdoc
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
            $value['datetime'] = true;
        }
        if (!empty($value['to']) && !$this->get_column()->get_filter_time()) {
            $datetime_to = $value['to'];
            //calculate end date considering timezone specification
            /** @var $datetimeTo \DateTime */
            $datetime_to->set_timezone(new \DateTimeZone($this->_scope_config->get_value($this->_locale_date->get_default_timezone_path(), \Magento\Store\Model\Scope_Interface::SCOPE_STORE)));
            $datetime_to->modify('+1 day')->modify('-1 second');
            $datetime_to->set_timezone(new \DateTimeZone('UTC'));
        }
        return $value;
    }
    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @return \DateTime|null
     */
    protected function _convert_date($date)
    {
        if ($this->get_column()->get_filter_time()) {
            try {
                $timezone = $this->get_column()->get_timezone() !== false ? $this->_locale_date->get_config_timezone() : 'UTC';
                $admin_time_zone = new \DateTimeZone($timezone);
                $simple_res = new \DateTime($date, $admin_time_zone);
                $simple_res->set_timezone(new \DateTimeZone('UTC'));
                return $simple_res;
            } catch (\Exception $e) {
                return null;
            }
        }
        return parent::_convert_date($date);
    }
    /**
     * Render filter html
     *
     * @return string
     */
    public function get_html()
    {
        $html_id = $this->math_random->get_unique_hash($this->_get_html_id());
        $format = $this->_locale_date->get_date_format(\Intl_Date_Formatter::SHORT);
        $time_format = '';
        if ($this->get_column()->get_filter_time()) {
            $time_format = $this->_locale_date->get_time_format(\Intl_Date_Formatter::SHORT);
        }
        $html = '<div class="range" id="' . $html_id . '_range"><div class="range-line date">' . '<input type="text" name="' . $this->_get_html_name() . '[from]" id="' . $html_id . '_from"' . ' value="' . $this->get_escaped_value('from') . '" class="input-text admin__control-text no-changes" placeholder="' . __('From') . '" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'from') . '/>' . '</div>';
        $html .= '<div class="range-line date">' . '<input type="text" name="' . $this->_get_html_name() . '[to]" id="' . $html_id . '_to"' . ' value="' . $this->get_escaped_value('to') . '" class="input-text admin__control-text no-changes" placeholder="' . __('To') . '" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'to') . '/>' . '</div></div>';
        $html .= '<input type="hidden" name="' . $this->_get_html_name() . '[locale]"' . ' value="' . $this->locale_resolver->get_locale() . '"/>';
        $script_string = 'require(["jquery", "mage/calendar"],function($){
                    $("#' . $html_id . '_range").dateRange({
                        dateFormat: "' . $format . '",
                        timeFormat: "' . $time_format . '",
                        showsTime: ' . ($this->get_column()->get_filter_time() ? 'true' : 'false') . ',
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
     * Return escaped value for calendar
     *
     * @param string|null $index
     * @return array|string|int|float|null
     */
    public function get_escaped_value($index = null)
    {
        if ($this->get_column()->get_filter_time()) {
            $value = $this->get_value($index);
            if ($value instanceof \DateTimeInterface) {
                return $this->_locale_date->format_date_time($value);
            }
            if (is_string($value)) {
                return $this->escape_html($value);
            }
            return $value;
        }
        return parent::get_escaped_value($index);
    }
}