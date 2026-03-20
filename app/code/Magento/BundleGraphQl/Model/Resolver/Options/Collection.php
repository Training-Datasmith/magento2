<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Options;

use Magento\Bundle\Model\Option_Factory;
use Magento\Framework\Api\Extension_Attribute\Join_Processor_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Collection to fetch bundle option data at resolution time.
 */
class Collection implements Reset_After_Request_Interface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';
    /**
     * @var OptionFactory
     */
    private $bundle_option_factory;
    /**
     * @var JoinProcessorInterface
     */
    private $extension_attributes_join_processor;
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var string[]
     */
    private $sku_map = [];
    /**
     * @var array
     */
    private $option_map = [];
    /** @var Uid */
    private $uid_encoder;
    /**
     * @param OptionFactory $bundleOptionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     * @param Uid|null $uidEncoder
     */
    public function __construct(Option_Factory $bundle_option_factory, Join_Processor_Interface $extension_attributes_join_processor, Store_Manager_Interface $store_manager, ?Uid $uid_encoder = null)
    {
        $this->bundle_option_factory = $bundle_option_factory;
        $this->extension_attributes_join_processor = $extension_attributes_join_processor;
        $this->store_manager = $store_manager;
        $this->uid_encoder = $uid_encoder ?: Object_Manager::get_instance()->get(Uid::class);
    }
    /**
     * Add parent id/sku pair to use for option filter at fetch time.
     *
     * @param int $parentId
     * @param int $parentEntityId
     * @param string $sku
     */
    public function add_parent_filter_data(int $parent_id, int $parent_entity_id, string $sku): void
    {
        $this->sku_map[$parent_id] = ['sku' => $sku, 'entity_id' => $parent_entity_id];
    }
    /**
     * Fetch data for bundle options and return the options for the given parent id.
     *
     * @param int $parentId
     * @return array
     */
    public function get_options_by_parent_id(int $parent_id): array
    {
        $options = $this->fetch();
        return $options[$parent_id] ?? [];
    }
    /**
     * Fetch bundle option data and return in array format. Keys for bundle options will be their parent product ids.
     *
     * @return array
     */
    private function fetch(): array
    {
        if (empty($this->sku_map) || !empty($this->option_map)) {
            return $this->option_map;
        }
        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $options_collection = $this->bundle_option_factory->create()->get_resource_collection();
        // All products in collection will have same store id.
        $options_collection->join_values($this->store_manager->get_store()->get_id());
        $product_table = $options_collection->get_table('catalog_product_entity');
        $link_field = $options_collection->get_connection()->get_auto_increment_field($product_table);
        $entity_ids = array_column($this->sku_map, 'entity_id');
        $options_collection->get_select()->join(['cpe' => $product_table], 'cpe.' . $link_field . ' = main_table.parent_id', [])->where('cpe.entity_id IN (?)', $entity_ids);
        $options_collection->set_position_order();
        $this->extension_attributes_join_processor->process($options_collection);
        if (empty($options_collection->get_data())) {
            return [];
        }
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options_collection as $option) {
            if (!isset($this->option_map[$option->get_parent_id()])) {
                $this->option_map[$option->get_parent_id()] = [];
            }
            $this->option_map[$option->get_parent_id()][$option->get_id()] = $option->get_data();
            $this->option_map[$option->get_parent_id()][$option->get_id()]['title'] = $option->get_title() === null ? $option->get_default_title() : $option->get_title();
            $this->option_map[$option->get_parent_id()][$option->get_id()]['sku'] = $this->sku_map[$option->get_parent_id()]['sku'];
            $this->option_map[$option->get_parent_id()][$option->get_id()]['uid'] = $this->uid_encoder->encode(self::OPTION_TYPE . '/' . $option->get_option_id());
        }
        return $this->option_map;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->option_map = [];
        $this->sku_map = [];
    }
}