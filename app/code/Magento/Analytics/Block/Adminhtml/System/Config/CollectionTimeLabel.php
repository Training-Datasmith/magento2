<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Provides label with default Time Zone
 */
class CollectionTimeLabel extends Field
{
    public function __construct(
        Context $context,
        private readonly ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Add current time zone to comment, properly translated according to locale
     *
     *
     */
    public function render(AbstractElement $element): string
    {
        $timeZoneCode = $this->_localeDate->getConfigTimezone();
        $locale = $this->localeResolver->getLocale();
        $getLongTimeZoneName = \IntlTimeZone::createTimeZone($timeZoneCode)
            ->getDisplayName(false, \IntlTimeZone::DISPLAY_LONG, $locale);
        $element->setData(
            'comment',
            sprintf('%s (%s)', $getLongTimeZoneName, $timeZoneCode)
        );
        return parent::render($element);
    }
}
