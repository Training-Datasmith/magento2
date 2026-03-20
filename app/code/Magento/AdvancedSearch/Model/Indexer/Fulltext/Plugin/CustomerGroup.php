<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Resource_Model\Attribute;
use Magento\Catalog_Search\Model\Indexer\Fulltext;
use Magento\Catalog_Search\Model\Indexer\Fulltext\Plugin\Abstract_Plugin;
use Magento\Customer\Model\Resource_Model\Group;
use Magento\Framework\Indexer\Indexer_Registry;
use Magento\Framework\Model\Abstract_Model;
class Customer_Group extends Abstract_Plugin
{
    public function __construct(Indexer_Registry $indexer_registry, protected \Magento\Advanced_Search\Model\Client\Client_Options_Interface $client_options)
    {
        parent::__construct($indexer_registry);
    }
    /**
     * Invalidate indexer on customer group save
     *
     * @return Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function around_save(Group $subject, \Closure $proceed, Abstract_Model $group)
    {
        $need_invalidation = $group->is_object_new() || $group->data_has_changed_for('tax_class_id');
        $result = $proceed($group);
        if ($need_invalidation) {
            $this->indexer_registry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}