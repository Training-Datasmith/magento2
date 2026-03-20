<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\Option_Interface;
use Magento\Bundle\Api\Product_Link_Management_Interface;
use Magento\Bundle\Api\Product_Option_Repository_Interface as OptionRepository;
use Magento\Bundle\Model\Option\Save_Action;
use Magento\Bundle\Model\Product_Relations_Processor_Composite;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Operation\Extension_Interface;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Bundle product save handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save_Handler implements Extension_Interface
{
    /**
     * @var OptionRepository
     */
    private $option_repository;
    /**
     * @var ProductLinkManagementInterface
     */
    private $product_link_management;
    /**
     * @var SaveAction
     */
    private $option_save;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var CheckOptionLinkIfExist
     */
    private $check_option_link_if_exist;
    /**
     * @var ProductRelationsProcessorComposite
     */
    private $product_relations_processor_composite;
    /**
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param SaveAction $optionSave
     * @param MetadataPool $metadataPool
     * @param CheckOptionLinkIfExist|null $checkOptionLinkIfExist
     * @param ProductRelationsProcessorComposite|null $productRelationsProcessorComposite
     */
    public function __construct(Option_Repository $option_repository, Product_Link_Management_Interface $product_link_management, Save_Action $option_save, Metadata_Pool $metadata_pool, ?Check_Option_Link_If_Exist $check_option_link_if_exist = null, ?Product_Relations_Processor_Composite $product_relations_processor_composite = null)
    {
        $this->option_repository = $option_repository;
        $this->product_link_management = $product_link_management;
        $this->option_save = $option_save;
        $this->metadata_pool = $metadata_pool;
        $this->check_option_link_if_exist = $check_option_link_if_exist ?? Object_Manager::get_instance()->get(Check_Option_Link_If_Exist::class);
        $this->product_relations_processor_composite = $product_relations_processor_composite ?? Object_Manager::get_instance()->get(Product_Relations_Processor_Composite::class);
    }
    /**
     * Perform action on Bundle product relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     *
     * @return ProductInterface|object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var OptionInterface[] $bundleProductOptions */
        $bundle_product_options = $entity->get_extension_attributes()->get_bundle_product_options() ?: [];
        //Only processing bundle products.
        if ($entity->get_type_id() !== Type::TYPE_CODE || empty($bundle_product_options) && !$entity->get_drop_options()) {
            return $entity;
        }
        $existing_bundle_product_options = $this->option_repository->get_list($entity->get_sku());
        $existing_options_ids = !empty($existing_bundle_product_options) ? $this->get_option_ids($existing_bundle_product_options) : [];
        $option_ids = $this->get_option_ids($bundle_product_options);
        if (!$entity->get_copy_from_view()) {
            $this->process_removed_options($entity, $existing_options_ids, $option_ids);
            $this->save_options($entity, $bundle_product_options, $existing_bundle_product_options);
        } else {
            //save only labels and not selections + product links
            $this->save_options($entity, $bundle_product_options);
            $entity->set_copy_from_view(false);
        }
        $this->product_relations_processor_composite->process($entity, $existing_bundle_product_options, $bundle_product_options);
        return $entity;
    }
    /**
     * Remove option product links
     *
     * @param string $entitySku
     * @param OptionInterface $option
     *
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function remove_option_links($entity_sku, $option)
    {
        $links = $option->get_product_links();
        if (!empty($links)) {
            foreach ($links as $link) {
                $this->product_link_management->remove_child($entity_sku, $option->get_id(), $link->get_sku());
            }
        }
    }
    /**
     * Perform save for all options entities.
     *
     * @param ProductInterface $entity
     * @param array $options
     * @param array $existingBundleProductOptions
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function save_options(Product_Interface $entity, array $options, array $existing_bundle_product_options = []): void
    {
        $this->option_save->save_bulk($entity, $options, $existing_bundle_product_options);
    }
    /**
     * Get options ids from array of the options entities.
     *
     * @param array $options
     *
     * @return array
     */
    private function get_option_ids(array $options): array
    {
        $option_ids = [];
        if (!empty($options)) {
            /** @var OptionInterface $option */
            foreach ($options as $option) {
                if ($option->get_option_id()) {
                    $option_ids[] = (int) $option->get_option_id();
                }
            }
        }
        return $option_ids;
    }
    /**
     * Removes old options that no longer exists.
     *
     * @param ProductInterface $entity
     * @param array $existingOptionsIds
     * @param array $optionIds
     *
     * @return void
     */
    private function process_removed_options(Product_Interface $entity, array $existing_options_ids, array $option_ids): void
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $parent_id = $entity->get_data($metadata->get_link_field());
        foreach (array_diff($existing_options_ids, $option_ids) as $option_id) {
            $option = $this->option_repository->get($entity->get_sku(), $option_id);
            $option->set_parent_id($parent_id);
            $this->remove_option_links($entity->get_sku(), $option);
            $this->option_repository->delete($option);
        }
    }
}