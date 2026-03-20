<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Quote\Item\Option;

use Magento\Framework\Data_Object;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\Option\Comparator_Interface;
/**
 * Bundle quote item option comparator
 */
class Bundle_Selection_Attributes_Comparator implements Comparator_Interface
{
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }
    /**
     * @inheritdoc
     */
    public function compare(Data_Object $option1, Data_Object $option2): bool
    {
        $value1 = $option1->get_value() ? $this->serializer->unserialize($option1->get_value()) : [];
        $value2 = $option2->get_value() ? $this->serializer->unserialize($option2->get_value()) : [];
        $option1Id = isset($value1['option_id']) ? (int) $value1['option_id'] : null;
        $option2Id = isset($value2['option_id']) ? (int) $value2['option_id'] : null;
        return $option1Id === $option2Id;
    }
}