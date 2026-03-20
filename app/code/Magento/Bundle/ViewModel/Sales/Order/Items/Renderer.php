<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\View_Model\Sales\Order\Items;

use Magento\Framework\View\Element\Block\Argument_Interface;
use Magento\Sales\Api\Data\Order_Item_Interface;
use Magento\Sales\Model\Resource_Model\Order\Item\Collection_Factory;
/**
 * ViewModel for Bundle Items
 */
class Renderer implements Argument_Interface
{
    /**
     * @var CollectionFactory
     */
    private $item_collection_factory;
    /**
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(Collection_Factory $item_collection_factory)
    {
        $this->item_collection_factory = $item_collection_factory;
    }
    /**
     * Get Bundle Order Item Collection.
     *
     * @param int $orderId
     * @param int $parentId
     *
     * @return array|null
     */
    public function get_order_items(int $order_id, int $parent_id): ?array
    {
        $collection = $this->item_collection_factory->create();
        $collection->set_order_filter($order_id);
        $collection->add_field_to_filter([Order_Item_Interface::ITEM_ID, Order_Item_Interface::PARENT_ITEM_ID], [['eq' => $parent_id], ['eq' => $parent_id]]);
        $items = [];
        foreach ($collection ?? [] as $item) {
            $items[] = $item;
        }
        $collection->clear();
        return $items;
    }
}