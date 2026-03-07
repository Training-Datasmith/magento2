<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;

class CustomerGroup extends AbstractPlugin
{
    public function __construct(
        IndexerRegistry $indexerRegistry,
        protected \Magento\AdvancedSearch\Model\Client\ClientOptionsInterface $clientOptions
    ) {
        parent::__construct($indexerRegistry);
    }

    /**
     * Invalidate indexer on customer group save
     *
     * @return Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        Group $subject,
        \Closure $proceed,
        AbstractModel $group
    ) {
        $needInvalidation = $group->isObjectNew() || $group->dataHasChangedFor('tax_class_id');
        $result = $proceed($group);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
