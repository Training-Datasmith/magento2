<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Ui\Data_Provider\Product\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Locale\Resolver_Interface;
use Magento\Framework\Number_Formatter_Factory;
use Magento\Ui\Data_Provider\Modifier\Modifier_Interface;
use Number_Formatter;
/**
 * Modify product listing special price attributes
 */
class Special_Price_Attributes implements Modifier_Interface
{
    /**
     * @var ResolverInterface
     */
    private $locale_resolver;
    /**
     * @var array
     */
    private $price_attribute_list;
    /**
     * @var NumberFormatterFactory
     */
    private $number_formatter_factory;
    /**
     * PriceAttributes constructor.
     *
     * @param DirectoryCurrency $directoryCurrency
     * @param ResolverInterface $localeResolver
     * @param array $priceAttributeList
     * @param NumberFormatterFactory|null $numberFormatterFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Directory_Currency $directory_currency, Resolver_Interface $locale_resolver, array $price_attribute_list = [], ?Number_Formatter_Factory $number_formatter_factory = null)
    {
        $this->locale_resolver = $locale_resolver;
        $this->price_attribute_list = $price_attribute_list;
        $this->number_formatter_factory = $number_formatter_factory ?? Object_Manager::get_instance()->get(Number_Formatter_Factory::class);
    }
    /**
     * @inheritdoc
     */
    public function modify_data(array $data): array
    {
        if (empty($data) || empty($this->price_attribute_list)) {
            return $data;
        }
        $number_formatter = $this->number_formatter_factory->create(['locale' => $this->locale_resolver->get_locale(), 'style' => Number_Formatter::PERCENT]);
        $number_formatter->set_attribute(Number_Formatter::MIN_FRACTION_DIGITS, 6);
        foreach ($data['items'] as &$item) {
            foreach ($this->price_attribute_list as $price_attribute) {
                if (isset($item[$price_attribute]) && $item[Product_Interface::TYPE_ID] === Type::TYPE_CODE) {
                    $item[$price_attribute] = $number_formatter->format((float) $item[$price_attribute] / 100);
                }
            }
        }
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function modify_meta(array $meta): array
    {
        return $meta;
    }
}