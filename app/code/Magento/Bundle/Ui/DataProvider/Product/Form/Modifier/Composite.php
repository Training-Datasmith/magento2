<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Bundle\Api\Product_Option_Repository_Interface;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Product_Repository_Interface;
use Magento\Catalog\Model\Locator\Locator_Interface;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
use Magento\Framework\Object_Manager_Interface;
use Magento\Ui\Data_Provider\Modifier\Modifier_Interface;
/**
 * Class Bundle customizes Bundle product creation flow
 */
class Composite extends Abstract_Modifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;
    /**
     * @var array
     */
    protected $modifiers = [];
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $options_repository;
    /**
     * @var ProductRepositoryInterface
     */
    protected $product_repository;
    /**
     * @param LocatorInterface $locator
     * @param ObjectManagerInterface $objectManager
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param array $modifiers
     */
    public function __construct(Locator_Interface $locator, Object_Manager_Interface $object_manager, Product_Option_Repository_Interface $options_repository, Product_Repository_Interface $product_repository, array $modifiers = [])
    {
        $this->locator = $locator;
        $this->object_manager = $object_manager;
        $this->options_repository = $options_repository;
        $this->product_repository = $product_repository;
        $this->modifiers = $modifiers;
    }
    /**
     * {@inheritdoc}
     */
    public function modify_meta(array $meta)
    {
        if ($this->locator->get_product()->get_type_id() === Type::TYPE_CODE) {
            foreach ($this->modifiers as $bundle_class) {
                /** @var ModifierInterface $bundleModifier */
                $bundle_modifier = $this->object_manager->get($bundle_class);
                if (!$bundle_modifier instanceof Modifier_Interface) {
                    throw new \InvalidArgumentException('Type "' . $bundle_class . '" is not an instance of ' . Modifier_Interface::class);
                }
                $meta = $bundle_modifier->modify_meta($meta);
            }
        }
        return $meta;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modify_data(array $data)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->locator->get_product();
        $model_id = $product->get_id();
        $is_bundle_product = $product->get_type_id() === Type::TYPE_CODE;
        if ($is_bundle_product && $model_id) {
            $data[$model_id][Bundle_Panel::CODE_BUNDLE_OPTIONS][Bundle_Panel::CODE_BUNDLE_OPTIONS] = [];
            /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
            foreach ($this->options_repository->get_list($product->get_sku()) as $option) {
                $selections = [];
                /** @var \Magento\Bundle\Api\Data\LinkInterface $productLink */
                foreach ($option->get_product_links() as $product_link) {
                    $linked_product = $this->product_repository->get($product_link->get_sku());
                    $integer_qty = 1;
                    if ($linked_product->get_extension_attributes()->get_stock_item()) {
                        if ($linked_product->get_extension_attributes()->get_stock_item()->get_is_qty_decimal()) {
                            $integer_qty = 0;
                        }
                    }
                    $selections[] = ['selection_id' => $product_link->get_id(), 'option_id' => $product_link->get_option_id(), 'product_id' => $linked_product->get_id(), 'name' => $linked_product->get_name(), 'sku' => $linked_product->get_sku(), 'is_default' => $product_link->get_is_default() ? '1' : '0', 'selection_price_value' => $product_link->get_price(), 'selection_price_type' => $product_link->get_price_type(), 'selection_qty' => $integer_qty ? (int) $product_link->get_qty() : $product_link->get_qty(), 'selection_can_change_qty' => $product_link->get_can_change_quantity(), 'selection_qty_is_integer' => (bool) $integer_qty, 'position' => $product_link->get_position(), 'delete' => ''];
                }
                $data[$model_id][Bundle_Panel::CODE_BUNDLE_OPTIONS][Bundle_Panel::CODE_BUNDLE_OPTIONS][] = ['position' => $option->get_position(), 'option_id' => $option->get_option_id(), 'title' => $option->get_title(), 'default_title' => $option->get_default_title(), 'type' => $option->get_type(), 'required' => $option->get_required() ? '1' : '0', 'bundle_selections' => $selections];
            }
        }
        return $data;
    }
}