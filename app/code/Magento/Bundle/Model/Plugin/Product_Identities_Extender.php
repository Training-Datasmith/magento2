<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Plugin;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Add parent identities to product identities.
 */
class Product_Identities_Extender implements Reset_After_Request_Interface
{
    /**
     * @var BundleType
     */
    private $type;
    /**
     * @var array
     */
    private $cache_parent_ids_by_child = [];
    /**
     * @param BundleType $type
     */
    public function __construct(Bundle_Type $type)
    {
        $this->type = $type;
    }
    /**
     * Add parent identities to product identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return string[]
     */
    public function after_get_identities(Catalog_Product $product, array $identities)
    {
        if ($product->get_type_id() !== Bundle_Type::TYPE_CODE) {
            return $identities;
        }
        foreach ($this->get_parent_ids_by_child($product->get_entity_id()) as $parent_id) {
            $identities[] = Catalog_Product::CACHE_TAG . '_' . $parent_id;
        }
        return $identities;
    }
    /**
     * Get parent ids by child with cache use
     *
     * @param mixed $entityId
     * @return array
     */
    private function get_parent_ids_by_child($entity_id): array
    {
        if (!isset($this->cache_parent_ids_by_child[$entity_id])) {
            $this->cache_parent_ids_by_child[$entity_id] = $this->type->get_parent_ids_by_child($entity_id);
        }
        return $this->cache_parent_ids_by_child[$entity_id];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->cache_parent_ids_by_child = [];
    }
}