<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Links;

use Magento\Bundle\Model\Resource_Model\Selection\Collection as LinkCollection;
use Magento\Bundle\Model\Resource_Model\Selection\Collection_Factory;
use Magento\Bundle\Model\Selection;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Graph_Ql\Query\Enum_Lookup;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Zend_Db_Select_Exception;
/**
 * Collection to fetch link data at resolution time.
 */
class Collection implements Reset_After_Request_Interface
{
    /**
     * @var CollectionFactory
     */
    private $link_collection_factory;
    /**
     * @var EnumLookup
     */
    private $enum_lookup;
    /**
     * @var int[]
     */
    private $option_ids = [];
    /**
     * @var int[]
     */
    private $parent_ids = [];
    /**
     * @var array
     */
    private $links = [];
    /** @var Uid */
    private $uid_encoder;
    /**
     * @param CollectionFactory $linkCollectionFactory
     * @param EnumLookup $enumLookup
     * @param Uid|null $uidEncoder
     */
    public function __construct(Collection_Factory $link_collection_factory, Enum_Lookup $enum_lookup, ?Uid $uid_encoder = null)
    {
        $this->link_collection_factory = $link_collection_factory;
        $this->enum_lookup = $enum_lookup;
        $this->uid_encoder = $uid_encoder ?: Object_Manager::get_instance()->get(Uid::class);
    }
    /**
     * Add option and id filter pair to filter for fetch.
     *
     * @param int $optionId
     * @param int $parentId
     * @return void
     */
    public function add_id_filters(int $option_id, int $parent_id): void
    {
        if (!in_array($option_id, $this->option_ids)) {
            $this->option_ids[] = $option_id;
        }
        if (!in_array($parent_id, $this->parent_ids)) {
            $this->parent_ids[] = $parent_id;
        }
    }
    /**
     * Retrieve links for passed in option id.
     *
     * @param int $optionId
     * @return array
     * @throws NoSuchEntityException
     * @throws RuntimeException
     * @throws Zend_Db_Select_Exception
     */
    public function get_links_for_option_id(int $option_id): array
    {
        $links_list = $this->fetch();
        if (!isset($links_list[$option_id])) {
            return [];
        }
        return $links_list[$option_id];
    }
    /**
     * Fetch link data and return in array format. Keys for links will be their option Ids.
     *
     * @return array
     * @throws RuntimeException
     * @throws Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function fetch(): array
    {
        if (empty($this->option_ids) || empty($this->parent_ids) || !empty($this->links)) {
            return $this->links;
        }
        /** @var LinkCollection $linkCollection */
        $link_collection = $this->link_collection_factory->create();
        $link_collection->set_option_ids_filter($this->option_ids);
        $field = 'parent_product_id';
        foreach ($link_collection->get_select()->get_part('from') as $table_alias => $data) {
            if ($data['tableName'] == $link_collection->get_table('catalog_product_bundle_selection')) {
                $field = $table_alias . '.' . $field;
            }
        }
        $link_collection->get_select()->where($field . ' IN (?)', $this->parent_ids, \Zend_Db::INT_TYPE);
        /** @var Selection $link */
        foreach ($link_collection as $link) {
            $data = $link->get_data();
            $formatted_link = ['price' => $link->get_selection_price_value(), 'position' => $link->get_position(), 'id' => $link->get_selection_id(), 'uid' => $this->uid_encoder->encode((string) $link->get_selection_id()), 'qty' => (float) $link->get_selection_qty(), 'quantity' => (float) $link->get_selection_qty(), 'is_default' => (bool) $link->get_is_default(), 'price_type' => $this->enum_lookup->get_enum_value_from_field('PriceTypeEnum', (string) $link->get_selection_price_type()) ?: 'DYNAMIC', 'can_change_quantity' => $link->get_selection_can_change_qty()];
            $data = array_replace($data, $formatted_link);
            if (!isset($this->links[$link->get_option_id()])) {
                $this->links[$link->get_option_id()] = [];
            }
            $this->links[$link->get_option_id()][] = $data;
        }
        return $this->links;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->links = [];
        $this->option_ids = [];
        $this->parent_ids = [];
    }
}