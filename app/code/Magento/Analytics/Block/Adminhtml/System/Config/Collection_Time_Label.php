<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Locale\Resolver_Interface;
/**
 * Provides label with default Time Zone
 */
class Collection_Time_Label extends Field
{
    public function __construct(Context $context, private readonly Resolver_Interface $locale_resolver, array $data = [])
    {
        parent::__construct($context, $data);
    }
    /**
     * Add current time zone to comment, properly translated according to locale
     *
     *
     */
    public function render(Abstract_Element $element): string
    {
        $time_zone_code = $this->_locale_date->get_config_timezone();
        $locale = $this->locale_resolver->get_locale();
        $get_long_time_zone_name = \Intl_Time_Zone::create_time_zone($time_zone_code)->get_display_name(false, \Intl_Time_Zone::DISPLAY_LONG, $locale);
        $element->set_data('comment', sprintf('%s (%s)', $get_long_time_zone_name, $time_zone_code));
        return parent::render($element);
    }
}