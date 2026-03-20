<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Model\Option\Save_Action;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Repository for performing CRUD operations for a bundle product's options.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Option_Repository implements \Magento\Bundle\Api\Product_Option_Repository_Interface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product_repository;
    /**
     * @var Product\Type
     */
    protected $type;
    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    protected $option_factory;
    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option
     */
    protected $option_resource;
    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    protected $link_management;
    /**
     * @var Product\OptionList
     */
    protected $product_option_list;
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $data_object_helper;
    /**
     * @var SaveAction
     */
    private $option_save;
    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Product\Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param \Magento\Bundle\Model\ResourceModel\Option $optionResource
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement
     * @param Product\OptionList $productOptionList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param SaveAction $optionSave
     */
    public function __construct(\Magento\Catalog\Api\Product_Repository_Interface $product_repository, \Magento\Bundle\Model\Product\Type $type, \Magento\Bundle\Api\Data\Option_Interface_Factory $option_factory, \Magento\Bundle\Model\Resource_Model\Option $option_resource, \Magento\Bundle\Api\Product_Link_Management_Interface $link_management, \Magento\Bundle\Model\Product\Option_List $product_option_list, \Magento\Framework\Api\Data_Object_Helper $data_object_helper, Save_Action $option_save)
    {
        $this->product_repository = $product_repository;
        $this->type = $type;
        $this->option_factory = $option_factory;
        $this->option_resource = $option_resource;
        $this->link_management = $link_management;
        $this->product_option_list = $product_option_list;
        $this->data_object_helper = $data_object_helper;
        $this->option_save = $option_save;
    }
    /**
     * @inheritdoc
     */
    public function get($sku, $option_id)
    {
        $product = $this->get_product($sku);
        /** @var \Magento\Bundle\Model\Option $option */
        $option = $this->type->get_options_collection($product)->get_item_by_id($option_id);
        if (!$option || !$option->get_id()) {
            throw new No_Such_Entity_Exception(__("The option that was requested doesn't exist. Verify the entity and try again."));
        }
        $product_links = $this->link_management->get_children($product->get_sku(), $option_id);
        /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
        $option_data_object = $this->option_factory->create();
        $this->data_object_helper->populate_with_array($option_data_object, $option->get_data(), \Magento\Bundle\Api\Data\Option_Interface::class);
        $option_data_object->set_option_id($option->get_id());
        $option_data_object->set_title($option->get_title() === null ? $option->get_default_title() : $option->get_title());
        $option_data_object->set_sku($product->get_sku());
        $option_data_object->set_product_links($product_links);
        return $option_data_object;
    }
    /**
     * @inheritdoc
     */
    public function get_list($sku)
    {
        $product = $this->get_product($sku);
        return $this->get_list_by_product($product);
    }
    /**
     * Return list of product options
     *
     * @param ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function get_list_by_product(Product_Interface $product)
    {
        return $this->product_option_list->get_items($product);
    }
    /**
     * @inheritdoc
     */
    public function delete(\Magento\Bundle\Api\Data\Option_Interface $option)
    {
        try {
            $this->option_resource->delete($option);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\State_Exception(__('The option with "%1" ID can\'t be deleted.', $option->get_option_id()), $exception);
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function delete_by_id($sku, $option_id)
    {
        /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
        $option = $this->get($sku, $option_id);
        $has_been_deleted = $this->delete($option);
        return $has_been_deleted;
    }
    /**
     * @inheritdoc
     */
    public function save(\Magento\Catalog\Api\Data\Product_Interface $product, \Magento\Bundle\Api\Data\Option_Interface $option)
    {
        $saved_option = $this->option_save->save($product, $option);
        $product_to_save = $this->product_repository->get($product->get_sku());
        $this->product_repository->save($product_to_save);
        return $saved_option->get_option_id();
    }
    /**
     * Update option selections
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return $this
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function update_option_selection(\Magento\Catalog\Api\Data\Product_Interface $product, \Magento\Bundle\Api\Data\Option_Interface $option)
    {
        $option_id = $option->get_option_id();
        $existing_links = $this->link_management->get_children($product->get_sku(), $option_id);
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
            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $linksToDelete */
            $links_to_delete = $this->compare_links($existing_links, $links_to_update);
        }
        foreach ($links_to_update as $linked_product) {
            $this->link_management->save_child($product->get_sku(), $linked_product);
        }
        foreach ($links_to_delete as $linked_product) {
            $this->link_management->remove_child($product->get_sku(), $option->get_option_id(), $linked_product->get_sku());
        }
        foreach ($links_to_add as $linked_product) {
            $this->link_management->add_child($product, $option->get_option_id(), $linked_product);
        }
        return $this;
    }
    /**
     * Retrieve product by SKU
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function get_product($sku)
    {
        $product = $this->product_repository->get($sku, true, null, true);
        if ($product->get_type_id() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Input_Exception(__('This is implemented for bundle products only.'));
        }
        return $product;
    }
    /**
     * Computes the difference between given arrays.
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $firstArray
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $secondArray
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
}