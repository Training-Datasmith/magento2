<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\Link_Interface;
use Magento\Bundle\Api\Data\Link_Interface_Factory;
use Magento\Bundle\Api\Data\Option_Interface;
use Magento\Bundle\Api\Product_Link_Management_Add_Children_Interface;
use Magento\Bundle\Api\Product_Link_Management_Interface;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Resource_Model\Bundle;
use Magento\Bundle\Model\Resource_Model\Bundle_Factory;
use Magento\Bundle\Model\Resource_Model\Option\Collection_Factory;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Api\Product_Repository_Interface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\Data_Object_Helper;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Exception\Could_Not_Save_Exception;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Class used to manage bundle products links.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Link_Management implements Product_Link_Management_Interface, Product_Link_Management_Add_Children_Interface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $product_repository;
    /**
     * @var LinkInterfaceFactory
     */
    protected $link_factory;
    /**
     * @var BundleFactory
     */
    protected $bundle_factory;
    /**
     * @var SelectionFactory
     */
    protected $bundle_selection;
    /**
     * @var CollectionFactory
     */
    protected $option_collection;
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var DataObjectHelper
     */
    protected $data_object_helper;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param LinkInterfaceFactory $linkFactory
     * @param SelectionFactory $bundleSelection
     * @param BundleFactory $bundleFactory
     * @param CollectionFactory $optionCollection
     * @param StoreManagerInterface $storeManager
     * @param DataObjectHelper $dataObjectHelper
     * @param MetadataPool $metadataPool
     */
    public function __construct(Product_Repository_Interface $product_repository, Link_Interface_Factory $link_factory, Selection_Factory $bundle_selection, Bundle_Factory $bundle_factory, Collection_Factory $option_collection, Store_Manager_Interface $store_manager, Data_Object_Helper $data_object_helper, Metadata_Pool $metadata_pool)
    {
        $this->product_repository = $product_repository;
        $this->link_factory = $link_factory;
        $this->bundle_factory = $bundle_factory;
        $this->bundle_selection = $bundle_selection;
        $this->option_collection = $option_collection;
        $this->store_manager = $store_manager;
        $this->data_object_helper = $data_object_helper;
        $this->metadata_pool = $metadata_pool;
    }
    /**
     * @inheritDoc
     */
    public function get_children($product_sku, $option_id = null)
    {
        $product = $this->product_repository->get($product_sku, true);
        if ($product->get_type_id() != Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('This is implemented for bundle products only.'));
        }
        $children_list = [];
        foreach ($this->get_options($product) as $option) {
            if (!$option->get_selections() || $option_id !== null && $option->get_option_id() != $option_id) {
                continue;
            }
            /** @var Product $selection */
            foreach ($option->get_selections() as $selection) {
                $children_list[] = $this->build_link($selection, $product);
            }
        }
        return $children_list;
    }
    /**
     * @inheritDoc
     */
    public function add_child_by_product_sku($sku, $option_id, Link_Interface $linked_product)
    {
        /** @var Product $product */
        $product = $this->product_repository->get($sku, true);
        return $this->add_child($product, $option_id, $linked_product);
    }
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save_child($sku, Link_Interface $linked_product)
    {
        $product = $this->product_repository->get($sku, true);
        if ($product->get_type_id() != Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('The product with the "%1" SKU isn\'t a bundle product.', [$product->get_sku()]));
        }
        /** @var Product $linkProductModel */
        $link_product_model = $this->product_repository->get($linked_product->get_sku());
        if ($link_product_model->is_composite()) {
            throw new Input_Exception(__('The bundle product can\'t contain another composite product.'));
        }
        if (!$linked_product->get_id()) {
            throw new Input_Exception(__('The product link needs an ID field entered. Enter and try again.'));
        }
        /** @var Selection $selectionModel */
        $selection_model = $this->bundle_selection->create();
        $selection_model->load($linked_product->get_id());
        if (!$selection_model->get_id()) {
            throw new Input_Exception(__('The product link with the "%1" ID field wasn\'t found. Verify the ID and try again.', [$linked_product->get_id()]));
        }
        $selection_model = $this->map_product_link_to_bundle_selection_model($selection_model, $linked_product, $product, (int) $link_product_model->get_id());
        try {
            $selection_model->save();
        } catch (\Exception $e) {
            throw new Could_Not_Save_Exception(__('Could not save child: "%1"', $e->get_message()), $e);
        }
        return true;
    }
    /**
     * Linked product processing
     *
     * @param LinkInterface $linkedProduct
     * @param array $selections
     * @param int $optionId
     * @param ProductInterface $product
     * @param string $linkField
     * @param Bundle $resource
     * @return int
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function process_linked_product(Link_Interface $linked_product, array $selections, int $option_id, Product_Interface $product, string $link_field, Bundle $resource): int
    {
        $link_product_model = $this->product_repository->get($linked_product->get_sku());
        if ($link_product_model->is_composite()) {
            throw new Input_Exception(__('The bundle product can\'t contain another composite product.'));
        }
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] == $option_id && $selection['product_id'] == $link_product_model->get_entity_id() && $selection['parent_product_id'] == $product->get_data($link_field)) {
                    if (!$product->get_copy_from_view()) {
                        throw new Could_Not_Save_Exception(__('Child with specified sku: "%1" already assigned to product: "%2"', [$linked_product->get_sku(), $product->get_sku()]));
                    }
                }
            }
        }
        $selection_model = $this->bundle_selection->create();
        $selection_model->load($linked_product->get_id());
        $selection_model = $this->map_product_link_to_bundle_selection_model($selection_model, $linked_product, $product, (int) $link_product_model->get_entity_id());
        $selection_model->set_option_id($option_id);
        try {
            $selection_model->save();
            $resource->add_product_relation($product->get_data($link_field), $link_product_model->get_entity_id());
        } catch (\Exception $e) {
            throw new Could_Not_Save_Exception(__('Could not save child: "%1"', $e->get_message()), $e);
        }
        $linked_product->set_id($selection_model->get_id());
        $linked_product->set_selection_id($selection_model->get_id());
        $linked_product->set_option_id($option_id);
        return (int) $selection_model->get_id();
    }
    /**
     * Fill selection model with product link data
     *
     * @param Selection $selectionModel
     * @param LinkInterface $productLink
     * @param string $linkedProductId
     * @param string $parentProductId
     * @return Selection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated
     * @see mapProductLinkToBundleSelectionModel
     */
    protected function map_product_link_to_selection_model(Selection $selection_model, Link_Interface $product_link, $linked_product_id, $parent_product_id)
    {
        $selection_model->set_product_id($linked_product_id);
        $selection_model->set_parent_product_id($parent_product_id);
        if ($product_link->get_selection_id() !== null) {
            $selection_model->set_selection_id($product_link->get_selection_id());
        }
        if ($product_link->get_option_id() !== null) {
            $selection_model->set_option_id($product_link->get_option_id());
        }
        if ($product_link->get_position() !== null) {
            $selection_model->set_position($product_link->get_position());
        }
        if ($product_link->get_qty() !== null) {
            $selection_model->set_selection_qty($product_link->get_qty());
        }
        if ($product_link->get_price_type() !== null) {
            $selection_model->set_selection_price_type($product_link->get_price_type());
        }
        if ($product_link->get_price() !== null) {
            $selection_model->set_selection_price_value($product_link->get_price());
        }
        if ($product_link->get_can_change_quantity() !== null) {
            $selection_model->set_selection_can_change_qty($product_link->get_can_change_quantity());
        }
        if ($product_link->get_is_default() !== null) {
            $selection_model->set_is_default($product_link->get_is_default());
        }
        return $selection_model;
    }
    /**
     * Fill selection model with product link data.
     *
     * @param Selection $selectionModel
     * @param LinkInterface $productLink
     * @param ProductInterface $parentProduct
     * @param int $linkedProductId
     * @return Selection
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function map_product_link_to_bundle_selection_model(Selection $selection_model, Link_Interface $product_link, Product_Interface $parent_product, int $linked_product_id): Selection
    {
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $selection_model->set_product_id($linked_product_id);
        $selection_model->set_parent_product_id($parent_product->get_data($link_field));
        if ($product_link->get_selection_id() !== null) {
            $selection_model->set_selection_id($product_link->get_selection_id());
        }
        if ($product_link->get_option_id() !== null) {
            $selection_model->set_option_id($product_link->get_option_id());
        }
        if ($product_link->get_position() !== null) {
            $selection_model->set_position($product_link->get_position());
        }
        if ($product_link->get_qty() !== null) {
            $selection_model->set_selection_qty($product_link->get_qty());
        }
        if ($product_link->get_price_type() !== null) {
            $selection_model->set_selection_price_type($product_link->get_price_type());
        }
        if ($product_link->get_price() !== null) {
            $selection_model->set_selection_price_value($product_link->get_price());
        }
        if ($product_link->get_can_change_quantity() !== null) {
            $selection_model->set_selection_can_change_qty($product_link->get_can_change_quantity());
        }
        if ($product_link->get_is_default() !== null) {
            $selection_model->set_is_default($product_link->get_is_default());
        }
        $selection_model->set_website_id((int) $this->store_manager->get_store($parent_product->get_store_id())->get_website_id());
        return $selection_model;
    }
    /**
     * @inheritDoc
     */
    public function add_child(Product_Interface $product, $option_id, Link_Interface $linked_product)
    {
        if ($product->get_type_id() != Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('The product with the "%1" SKU isn\'t a bundle product.', $product->get_sku()));
        }
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $options = $this->option_collection->create();
        $options->set_id_filter($option_id);
        $options->set_product_link_filter($product->get_data($link_field));
        $existing_option = $options->get_first_item();
        if (!$existing_option->get_id()) {
            throw new Input_Exception(__('Product with specified sku: "%1" does not contain option: "%2"', [$product->get_sku(), $option_id]));
        }
        /* @var $resource Bundle */
        $resource = $this->bundle_factory->create();
        $selections = $resource->get_selections_data($product->get_data($link_field));
        return $this->process_linked_product($linked_product, $selections, (int) $option_id, $product, $link_field, $resource);
    }
    /**
     * @inheritDoc
     */
    public function add_children(Product_Interface $product, int $option_id, array $linked_products): void
    {
        if ($product->get_type_id() != Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('The product with the "%1" SKU isn\'t a bundle product.', $product->get_sku()));
        }
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        $options = $this->option_collection->create();
        $options->set_id_filter($option_id);
        $options->set_product_link_filter($product->get_data($link_field));
        $existing_option = $options->get_first_item();
        if (!$existing_option->get_id()) {
            throw new Input_Exception(__('Product with specified sku: "%1" does not contain option: "%2"', [$product->get_sku(), $option_id]));
        }
        /* @var $resource Bundle */
        $resource = $this->bundle_factory->create();
        $selections = $resource->get_selections_data($product->get_data($link_field));
        foreach ($linked_products as $linked_product) {
            $this->process_linked_product($linked_product, $selections, $option_id, $product, $link_field, $resource);
        }
    }
    /**
     * @inheritDoc
     */
    public function remove_child($sku, $option_id, $child_sku)
    {
        $product = $this->product_repository->get($sku, true);
        if ($product->get_type_id() != Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('The product with the "%1" SKU isn\'t a bundle product.', $sku));
        }
        $exclude_selection_ids = [];
        $used_product_ids = [];
        $remove_selection_ids = [];
        $remove_product_ids = [];
        foreach ($this->get_options($product) as $option) {
            /** @var Selection $selection */
            foreach ($option->get_selections() as $selection) {
                if (strcasecmp($selection->get_sku(), $child_sku) == 0 && $selection->get_option_id() == $option_id) {
                    $remove_selection_ids[] = $selection->get_selection_id();
                    $remove_product_ids[] = $selection->get_product_id();
                    continue;
                }
                $used_product_ids[] = $selection->get_product_id();
                $exclude_selection_ids[] = $selection->get_selection_id();
            }
        }
        if (empty($remove_selection_ids)) {
            throw new No_Such_Entity_Exception(__("The bundle product doesn't exist. Review your request and try again."));
        }
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        /* @var $resource Bundle */
        $resource = $this->bundle_factory->create();
        $resource->drop_all_unneeded_selections($product->get_data($link_field), $exclude_selection_ids);
        $product_relations_to_remove = array_diff($remove_product_ids, $used_product_ids);
        if ($product_relations_to_remove) {
            $resource->remove_product_relations($product->get_data($link_field), array_unique($product_relations_to_remove));
        }
        return true;
    }
    /**
     * Build bundle link between two products
     *
     * @param Product $selection
     * @param Product $product
     *
     * @return LinkInterface
     */
    private function build_link(Product $selection, Product $product)
    {
        $selection_price_type = $selection_price = null;
        /** @var Selection $product */
        if ($product->get_price_type()) {
            $selection_price_type = $selection->get_selection_price_type();
            $selection_price = $selection->get_selection_price_value();
        }
        /** @var LinkInterface $link */
        $link = $this->link_factory->create();
        $this->data_object_helper->populate_with_array($link, $selection->get_data(), Link_Interface::class);
        $link->set_is_default($selection->get_is_default())->set_id($selection->get_selection_id())->set_qty($selection->get_selection_qty())->set_can_change_quantity($selection->get_selection_can_change_qty())->set_price($selection_price)->set_price_type($selection_price_type);
        return $link;
    }
    /**
     * Get bundle product options
     *
     * @param ProductInterface $product
     *
     * @return OptionInterface[]
     */
    private function get_options(Product_Interface $product)
    {
        /** @var Type $productTypeInstance */
        $product_type_instance = $product->get_type_instance();
        $product_type_instance->set_store_filter($product->get_store_id(), $product);
        $option_collection = $product_type_instance->get_options_collection($product);
        $selection_collection = $product_type_instance->get_selections_collection($product_type_instance->get_options_ids($product), $product);
        return $option_collection->append_selections($selection_collection, true);
    }
}