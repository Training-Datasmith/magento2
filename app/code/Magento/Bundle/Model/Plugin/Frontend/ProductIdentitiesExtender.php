<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Plugin\Frontend;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Add child identities to product identities on storefront.
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
    private $cache_children_ids = [];
    /**
     * @param BundleType $type
     */
    public function __construct(Bundle_Type $type)
    {
        $this->type = $type;
    }
    /**
     * Add child identities to product identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return array
     */
    public function after_get_identities(Catalog_Product $product, array $identities): array
    {
        if ($product->get_type_id() !== Bundle_Type::TYPE_CODE) {
            return $identities;
        }
        foreach ($this->get_children_ids($product->get_entity_id()) as $child_ids) {
            foreach ($child_ids as $child_id) {
                $identities[] = Catalog_Product::CACHE_TAG . '_' . $child_id;
            }
        }
        return array_unique($identities);
    }
    /**
     * Get children ids with cache use
     *
     * @param mixed $entityId
     * @return array
     */
    private function get_children_ids($entity_id): array
    {
        if (!isset($this->cache_children_ids[$entity_id])) {
            $this->cache_children_ids[$entity_id] = $this->type->get_children_ids($entity_id);
        }
        return $this->cache_children_ids[$entity_id];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->cache_children_ids = [];
    }
}