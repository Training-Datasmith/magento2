<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Locator\Locator_Interface;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
/**
 * Class StockData hides unnecessary fields in Advanced Inventory Modal
 */
class Stock_Data extends Abstract_Modifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;
    /**
     * @param LocatorInterface $locator
     */
    public function __construct(Locator_Interface $locator)
    {
        $this->locator = $locator;
    }
    /**
     * {@inheritdoc}
     */
    public function modify_data(array $data)
    {
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function modify_meta(array $meta)
    {
        if ($this->locator->get_product()->get_type_id() === Type::TYPE_CODE) {
            $config['arguments']['data']['config'] = ['visible' => 0, 'imports' => ['visible' => null]];
            $meta['advanced_inventory_modal'] = ['children' => ['stock_data' => ['children' => ['qty' => $config, 'container_min_qty' => $config, 'container_min_sale_qty' => $config, 'container_max_sale_qty' => $config, 'is_qty_decimal' => $config, 'is_decimal_divided' => $config, 'container_backorders' => $config, 'container_notify_stock_qty' => $config]]]];
        }
        return $meta;
    }
}