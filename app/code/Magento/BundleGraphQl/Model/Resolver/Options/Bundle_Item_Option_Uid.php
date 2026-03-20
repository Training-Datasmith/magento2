<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Options;

use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Exception\Graph_Ql_Input_Exception;
use Magento\Framework\Graph_Ql\Query\Resolver\Context_Interface;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * Format new option uid in base64 encode for entered bundle options
 */
class Bundle_Item_Option_Uid implements Resolver_Interface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';
    /** @var Uid */
    private $uid_encoder;
    /**
     * @param Uid $uidEncoder
     */
    public function __construct(Uid $uid_encoder)
    {
        $this->uid_encoder = $uid_encoder;
    }
    /**
     * Create a option uid for entered option in "<option-type>/<option-id>/<option-value-id>/<quantity>" format
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return string
     *
     * @throws GraphQlInputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['option_id']) || empty($value['option_id'])) {
            throw new Graph_Ql_Input_Exception(__('"option_id" value should be specified.'));
        }
        if (!isset($value['selection_id']) || empty($value['selection_id'])) {
            throw new Graph_Ql_Input_Exception(__('"selection_id" value should be specified.'));
        }
        $option_details = [self::OPTION_TYPE, $value['option_id'], $value['selection_id'], (int) $value['selection_qty']];
        $content = implode('/', $option_details);
        return $this->uid_encoder->encode($content);
    }
}