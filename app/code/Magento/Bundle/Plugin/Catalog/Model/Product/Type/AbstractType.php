<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Catalog\Model\Product\Type;

use Magento\Bundle\Model\Product\Single_Choice_Provider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Abstract_Type as Subject;
/**
 * Plugin to add possibility to add bundle product with single option from list
 */
class Abstract_Type
{
    /**
     * @var SingleChoiceProvider
     */
    private $single_choice_provider;
    /**
     * @param SingleChoiceProvider $singleChoiceProvider
     */
    public function __construct(Single_Choice_Provider $single_choice_provider)
    {
        $this->single_choice_provider = $single_choice_provider;
    }
    /**
     * Add possibility to add to cart from the list in case of one required option
     *
     * @param Subject $subject
     * @param bool $result
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_is_possible_buy_from_list(Subject $subject, $result, $product)
    {
        if ($product->get_type_id() === Type::TYPE_BUNDLE) {
            $is_single_choice = $this->single_choice_provider->is_single_choice_available($product);
            if ($is_single_choice === true) {
                $result = $is_single_choice;
            }
        }
        return $result;
    }
}