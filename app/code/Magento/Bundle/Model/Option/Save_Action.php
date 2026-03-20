<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Option;

use Exception;
use Magento\Bundle\Api\Data\Link_Interface;
use Magento\Bundle\Api\Data\Option_Interface;
use Magento\Bundle\Api\Product_Link_Management_Add_Children_Interface;
use Magento\Bundle\Api\Product_Link_Management_Interface;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Resource_Model\Option;
use Magento\Bundle\Model\Resource_Model\Option\Collection;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Entity_Manager\Entity_Metadata_Interface;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Exception\Could_Not_Save_Exception;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Encapsulates logic for saving a bundle option, including coalescing the parent product's data.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save_Action
{
    /**
     * @var Option
     */
    private $option_resource;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var ProductLinkManagementInterface
     */
    private $link_management;
    /**
     * @var ProductLinkManagementAddChildrenInterface
     */
    private $add_children;
    /**
     * @param Option $optionResource
     * @param MetadataPool $metadataPool
     * @param Type $type
     * @param ProductLinkManagementInterface $linkManagement
     * @param StoreManagerInterface|null $storeManager
     * @param ProductLinkManagementAddChildrenInterface|null $addChildren
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Option $option_resource, Metadata_Pool $metadata_pool, Type $type, Product_Link_Management_Interface $link_management, ?Store_Manager_Interface $store_manager = null, ?Product_Link_Management_Add_Children_Interface $add_children = null)
    {
        $this->option_resource = $option_resource;
        $this->metadata_pool = $metadata_pool;
        $this->type = $type;
        $this->link_management = $link_management;
        $this->add_children = $add_children ?: Object_Manager::get_instance()->get(Product_Link_Management_Add_Children_Interface::class);
    }
    /**
     * Bulk options save
     *
     * @param ProductInterface $bundleProduct
     * @param array $options
     * @param array $existingBundleProductOptions
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function save_bulk(Product_Interface $bundle_product, array $options, array $existing_bundle_product_options = []): void
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $option_collection = $this->type->get_options_collection($bundle_product);
        foreach ($options as $option) {
            $this->save_option_item($bundle_product, $option, $option_collection, $metadata, $existing_bundle_product_options);
        }
        $bundle_product->set_is_relations_changed(true);
    }
    /**
     * Process option save
     *
     * @param ProductInterface $bundleProduct
     * @param OptionInterface $option
     * @param Collection $optionCollection
     * @param EntityMetadataInterface $metadata
     * @param array $existingBundleProductOptions
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function save_option_item(Product_Interface $bundle_product, Option_Interface $option, Collection $option_collection, Entity_Metadata_Interface $metadata, array $existing_bundle_product_options = []): void
    {
        $links_to_add = [];
        $option->set_store_id($bundle_product->get_store_id());
        $parent_id = $bundle_product->get_data($metadata->get_link_field());
        $option->set_parent_id($parent_id);
        $option_id = $option->get_option_id();
        $existing_option = $this->retrieve_existing_option($option_collection, $option, $existing_bundle_product_options);
        if (!$option_id || $existing_option->get_parent_id() != $parent_id) {
            $option->set_option_id(null);
            $option->set_default_title($option->get_title());
            if (is_array($option->get_product_links())) {
                $links_to_add = $option->get_product_links();
            }
        } else {
            if (!$existing_option || !$existing_option->get_option_id()) {
                throw new No_Such_Entity_Exception(__("The option that was requested doesn't exist. Verify the entity and try again."));
            }
            $option->set_data(array_merge($existing_option->get_data(), $option->get_data()));
            $this->update_option_selection($bundle_product, $option, $existing_option);
        }
        try {
            $this->option_resource->save($option);
        } catch (Exception $e) {
            throw new Could_Not_Save_Exception(__("The option couldn't be saved."), $e);
        }
        /** @var LinkInterface $linkedProduct */
        foreach ($links_to_add as $linked_product) {
            $this->link_management->add_child($bundle_product, $option->get_option_id(), $linked_product);
        }
    }
    /**
     * Manage the logic of saving a bundle option, including the coalescence of its parent product data.
     *
     * @param ProductInterface $bundleProduct
     * @param OptionInterface $option
     * @return OptionInterface
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function save(Product_Interface $bundle_product, Option_Interface $option)
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $option_collection = $this->type->get_options_collection($bundle_product);
        $this->save_option_item($bundle_product, $option, $option_collection, $metadata);
        return $option;
    }
    /**
     * Update option selections
     *
     * @param ProductInterface $product
     * @param OptionInterface $option
     * @param OptionInterface|null $existingOption
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function update_option_selection(Product_Interface $product, Option_Interface $option, ?Option_Interface $existing_option = null): void
    {
        $links_to_add = [];
        $links_to_update = [];
        $links_to_delete = [];
        if (is_array($option->get_product_links())) {
            $product_links = $option->get_product_links();
            foreach ($product_links as $product_link) {
                if (!$product_link->get_id() && !$product_link->get_selection_id()) {
                    $links_to_add[] = $product_link;
                } else {
                    $links_to_update[] = $product_link;
                }
            }
            if (!empty($existing_option) && !empty($existing_option->get_product_links())) {
                $links_to_delete = $this->compare_links($existing_option->get_product_links(), $links_to_update);
                $links_to_update = $this->verify_links_to_update($existing_option->get_product_links(), $links_to_update);
            }
        }
        foreach ($links_to_update as $linked_product) {
            $this->link_management->save_child($product->get_sku(), $linked_product);
        }
        foreach ($links_to_delete as $linked_product) {
            $this->link_management->remove_child($product->get_sku(), $option->get_option_id(), $linked_product->get_sku());
        }
        $this->add_children->add_children($product, (int) $option->get_option_id(), $links_to_add);
    }
    /**
     * Verify that updated data actually changed
     *
     * @param LinkInterface[] $existing
     * @param LinkInterface[] $updates
     * @return array
     */
    private function verify_links_to_update(array $existing, array $updates): array
    {
        $links_to_update = [];
        $before_links_map = [];
        foreach ($existing as $before_link) {
            $before_links_map[$before_link->get_id()] = $before_link;
        }
        foreach ($updates as $updated_link) {
            if (array_key_exists($updated_link->get_id(), $before_links_map)) {
                $before_link = $before_links_map[$updated_link->get_id()];
                if ($this->is_link_changed($before_link, $updated_link)) {
                    $links_to_update[] = $updated_link;
                }
            } else {
                $links_to_update[] = $updated_link;
            }
        }
        return $links_to_update;
    }
    /**
     * Check is updated link actually updated
     *
     * @param LinkInterface $beforeLink
     * @param LinkInterface $updatedLink
     * @return bool
     */
    private function is_link_changed(Link_Interface $before_link, Link_Interface $updated_link): bool
    {
        return (int) $before_link->get_option_id() !== (int) $updated_link->get_option_id() || $before_link->get_is_default() !== $updated_link->get_is_default() || (float) $before_link->get_qty() !== (float) $updated_link->get_qty() || $before_link->get_price() !== $updated_link->get_price() || $before_link->get_can_change_quantity() !== $updated_link->get_can_change_quantity() || (array) $before_link->get_extension_attributes() !== (array) $updated_link->get_extension_attributes() || (int) $before_link->get_position() !== (int) $updated_link->get_position() || $before_link->get_sku() !== $updated_link->get_sku() || $before_link->get_price_type() !== $updated_link->get_price_type();
    }
    /**
     * Compute the difference between given arrays.
     *
     * @param LinkInterface[] $firstArray
     * @param LinkInterface[] $secondArray
     *
     * @return array
     */
    private function compare_links(array $first_array, array $second_array)
    {
        $result = [];
        $first_array_ids = [];
        $first_array_map = [];
        $second_array_ids = [];
        foreach ($first_array as $item) {
            $first_array_ids[] = $item->get_id();
            $first_array_map[$item->get_id()] = $item;
        }
        foreach ($second_array as $item) {
            $second_array_ids[] = $item->get_id();
        }
        foreach (array_diff($first_array_ids, $second_array_ids) as $id) {
            $result[] = $first_array_map[$id];
        }
        return $result;
    }
    /**
     * Retrieve option from list.
     *
     * @param Collection $optionCollection
     * @param OptionInterface $option
     * @param array $existingBundleProductOptions
     * @return OptionInterface
     */
    private function retrieve_existing_option(Collection $option_collection, Option_Interface $option, array $existing_bundle_product_options): Option_Interface
    {
        $existing_option = $option_collection->get_item_by_id($option->get_option_id());
        $incoming_option = current(array_filter($existing_bundle_product_options, function ($obj) use ($option) {
            return $obj->get_data()['option_id'] == $option->get_id();
        }));
        if (!empty($incoming_option)) {
            $existing_option->set_data(array_merge($existing_option->get_data(), $incoming_option->get_data()));
        }
        // @phpstan-ignore-next-line
        if (empty($existing_option)) {
            $existing_option = $option_collection->get_new_empty_item();
        }
        return $existing_option;
    }
}