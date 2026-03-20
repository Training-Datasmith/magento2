<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Wishlist;

use Magento\Bundle\Model\Product\Bundle_Option_Data_Provider;
use Magento\Catalog\Model\Product\Configuration\Item\Item_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * Fetches the selected bundle options
 */
class Bundle_Options implements Resolver_Interface
{
    /**
     * @var BundleOptionDataProvider
     */
    private $bundle_option_data_provider;
    /**
     * @param BundleOptionDataProvider $bundleOptionDataProvider
     */
    public function __construct(Bundle_Option_Data_Provider $bundle_option_data_provider)
    {
        $this->bundle_option_data_provider = $bundle_option_data_provider;
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!$value['itemModel'] instanceof Item_Interface) {
            throw new Localized_Exception(__('"itemModel" should be a "%instance" instance', ['instance' => Item_Interface::class]));
        }
        return $this->bundle_option_data_provider->get_data($value['itemModel']);
    }
}