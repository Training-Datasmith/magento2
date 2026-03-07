<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Catalog\ViewModel\Product;

use Magento\Bundle\Model\Product\SingleChoiceProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\ViewModel\Product\OptionsData as Subject;

/**
 * Plugin to add bundle options data
 */
class AddBundleOptionsData
{
    /**
     * @var SingleChoiceProvider
     */
    private $singleChoiceProvider;

    /**
     * @param SingleChoiceProvider $singleChoiceProvider
     */
    public function __construct(
        SingleChoiceProvider $singleChoiceProvider
    ) {
        $this->singleChoiceProvider = $singleChoiceProvider;
    }

    public function afterGetOptionsData(Subject $subject, array $result, Product $product): array
    {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            if ($this->singleChoiceProvider->isSingleChoiceAvailable($product) === true) {
                $typeInstance = $product->getTypeInstance();
                $typeInstance->setStoreFilter($product->getStoreId(), $product);
                $options = $typeInstance->getOptions($product);
                foreach ($options as $option) {
                    $optionId = $option->getId();
                    $selectionsCollection = $typeInstance->getSelectionsCollection(
                        [$optionId],
                        $product
                    );
                    $selections = $selectionsCollection->exportToArray();
                    $countSelections = count($selections);
                    foreach ($selections as $selection) {
                        $name = 'bundle_option[' . $optionId . ']';
                        if ($countSelections > 1) {
                            $name .= '[]';
                        }
                        $result[] = [
                            'name' => $name,
                            'value' => $selection['selection_id'],
                        ];
                    }
                }
            }
        }
        return $result;
    }
}
