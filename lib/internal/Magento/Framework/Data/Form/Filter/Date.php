<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Filter;

use Exception;
use Magento\Framework\Filter\Localized_To_Normalized;
use Magento\Framework\Filter\Normalized_To_Localized;
use Magento\Framework\Locale\Resolver_Interface;
use Magento\Framework\Stdlib\DateTime;
/**
 * Form Input/Output Strip HTML tags Filter
 */
class Date implements Filter_Interface
{
    /**
     * @var string
     */
    protected $_date_format;
    /**
     * @var ResolverInterface
     */
    protected $locale_resolver;
    /**
     * Initialize filter
     *
     * @param string|null $format \DateTime input/output format
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(?string $format = null, ?Resolver_Interface $locale_resolver = null)
    {
        $this->_date_format = $format ?? DateTime::DATE_INTERNAL_FORMAT;
        $this->locale_resolver = $locale_resolver;
    }
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function input_filter($value)
    {
        if (!$value) {
            return $value;
        }
        $filter_input = new Localized_To_Normalized(['date_format' => $this->_date_format, 'locale' => $this->locale_resolver->get_locale()]);
        $filter_internal = new Normalized_To_Localized(['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->locale_resolver->get_locale()]);
        $value = $filter_input->filter($value);
        return $filter_internal->filter($value);
    }
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function output_filter($value)
    {
        if (!$value) {
            return $value;
        }
        $filter_input = new Localized_To_Normalized(['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->locale_resolver->get_locale()]);
        $filter_internal = new Normalized_To_Localized(['date_format' => $this->_date_format, 'locale' => $this->locale_resolver->get_locale()]);
        $value = $filter_input->filter($value);
        return $filter_internal->filter($value);
    }
}