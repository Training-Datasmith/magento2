<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
/**
 * Disable Quantity field by default
 */
class Bundle_Quantity extends Abstract_Modifier
{
    public const CODE_QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';
    public const CODE_QUANTITY = 'qty';
    public const CODE_QTY_CONTAINER = 'quantity_and_stock_status_qty';
    /**
     * {@inheritdoc}
     */
    public function modify_meta(array $meta)
    {
        if ($group_code = $this->get_group_code_by_field($meta, 'container_' . self::CODE_QUANTITY_AND_STOCK_STATUS)) {
            $parent_children =& $meta[$group_code]['children'];
            if (!empty($parent_children['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS])) {
                $parent_children['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS] = array_replace_recursive($parent_children['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS], ['children' => [self::CODE_QUANTITY_AND_STOCK_STATUS => ['arguments' => ['data' => ['config' => ['disabled' => false]]]]]]);
            }
        }
        if ($group_code = $this->get_group_code_by_field($meta, self::CODE_QTY_CONTAINER)) {
            $parent_children =& $meta[$group_code]['children'];
            if (!empty($parent_children[self::CODE_QTY_CONTAINER])) {
                $parent_children[self::CODE_QTY_CONTAINER] = array_replace_recursive($parent_children[self::CODE_QTY_CONTAINER], ['children' => [self::CODE_QUANTITY => ['arguments' => ['data' => ['config' => ['disabled' => true]]]]]]);
            }
        }
        return $meta;
    }
    /**
     * {@inheritdoc}
     */
    public function modify_data(array $data)
    {
        return $data;
    }
}