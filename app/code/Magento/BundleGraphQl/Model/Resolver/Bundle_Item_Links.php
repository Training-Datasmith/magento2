<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver;

use Magento\Bundle_Graph_Ql\Model\Resolver\Links\Collection;
use Magento\Bundle_Graph_Ql\Model\Resolver\Links\Collection_Factory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver\Value_Factory;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * @inheritdoc
 */
class Bundle_Item_Links implements Resolver_Interface
{
    /**
     * @var CollectionFactory
     */
    private Collection_Factory $link_collection_factory;
    /**
     * @var ValueFactory
     */
    private Value_Factory $value_factory;
    /**
     * @param Collection $linkCollection Deprecated. Use $linkCollectionFactory instead
     * @param ValueFactory $valueFactory
     * @param CollectionFactory|null $linkCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Collection $link_collection, Value_Factory $value_factory, ?Collection_Factory $link_collection_factory = null)
    {
        $this->link_collection_factory = $link_collection_factory ?: Object_Manager::get_instance()->get(Collection_Factory::class);
        $this->value_factory = $value_factory;
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['option_id']) || !isset($value['parent_id'])) {
            throw new Localized_Exception(__('"option_id" and "parent_id" values should be specified'));
        }
        $link_collection = $this->link_collection_factory->create();
        $link_collection->add_id_filters((int) $value['option_id'], (int) $value['parent_id']);
        $result = function () use ($value, $link_collection) {
            return $link_collection->get_links_for_option_id((int) $value['option_id']);
        };
        return $this->value_factory->create($result);
    }
}