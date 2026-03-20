<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver;

use Magento\Bundle_Graph_Ql\Model\Cart\Bundle_Option_Data_Provider;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Exception\Graph_Ql_Input_Exception;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * Resolver for bundle product options
 */
class Bundle_Option implements Resolver_Interface
{
    /**
     * @var BundleOptionDataProvider
     */
    private $data_provider;
    /**
     * @param BundleOptionDataProvider $bundleOptionDataProvider
     */
    public function __construct(Bundle_Option_Data_Provider $bundle_option_data_provider)
    {
        $this->data_provider = $bundle_option_data_provider;
    }
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new Graph_Ql_Input_Exception(__('Value must contain "model" property.'));
        }
        return $this->data_provider->get_data($value['model']);
    }
}