<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Order\Create;

class Sidebar
{
    /**
     * Get item qty
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject
     * @param callable $proceed
     * @param \Magento\Framework\DataObject $item
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function around_get_item_qty(\Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\Abstract_Sidebar $subject, \Closure $proceed, \Magento\Framework\Data_Object $item)
    {
        if ($item->get_product()->get_type_id() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            return '';
        }
        return $proceed($item);
    }
    /**
     * Check whether product configuration is required before adding to order
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject
     * @param callable $proceed
     * @param string $productType
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function around_is_configuration_required(\Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\Abstract_Sidebar $subject, \Closure $proceed, $product_type)
    {
        if ($product_type == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            return true;
        }
        return $proceed($product_type);
    }
}