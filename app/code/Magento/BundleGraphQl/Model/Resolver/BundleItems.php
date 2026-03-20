<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle_Graph_Ql\Model\Resolver\Options\Collection;
use Magento\Bundle_Graph_Ql\Model\Resolver\Options\Collection_Factory;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver\Value_Factory;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
/**
 * @inheritdoc
 */
class Bundle_Items implements Resolver_Interface
{
    /**
     * @var CollectionFactory
     */
    private Collection_Factory $bundle_option_collection_factory;
    /**
     * @var ValueFactory
     */
    private Value_Factory $value_factory;
    /**
     * @var MetadataPool
     */
    private Metadata_Pool $metadata_pool;
    /**
     * @param Collection $bundleOptionCollection Deprecated. Use $bundleOptionCollectionFactory
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     * @param CollectionFactory|null $bundleOptionCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Collection $bundle_option_collection, Value_Factory $value_factory, Metadata_Pool $metadata_pool, ?Collection_Factory $bundle_option_collection_factory = null)
    {
        $this->bundle_option_collection_factory = $bundle_option_collection_factory ?: Object_Manager::get_instance()->get(Collection_Factory::class);
        $this->value_factory = $value_factory;
        $this->metadata_pool = $metadata_pool;
    }
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        $link_field = $this->metadata_pool->get_metadata(Product_Interface::class)->get_link_field();
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value[$link_field]) || !isset($value[Product_Interface::SKU])) {
            $result = function () {
                return null;
            };
            return $this->value_factory->create($result);
        }
        $bundle_option_collection = $this->bundle_option_collection_factory->create();
        $bundle_option_collection->add_parent_filter_data((int) $value[$link_field], (int) $value['entity_id'], $value[Product_Interface::SKU]);
        $result = function () use ($value, $link_field, $bundle_option_collection) {
            return $bundle_option_collection->get_options_by_parent_id((int) $value[$link_field]);
        };
        return $this->value_factory->create($result);
    }
}