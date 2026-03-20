<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\View_Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * ViewModel for Bundle Option Block
 */
class Validate_Quantity implements Argument_Interface
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
     * Returns quantity validator.
     *
     * @return string
     */
    public function get_quantity_validators(): string
    {
        $validators['validate-item-quantity'] = [];
        return $this->serializer->serialize($validators);
    }
}