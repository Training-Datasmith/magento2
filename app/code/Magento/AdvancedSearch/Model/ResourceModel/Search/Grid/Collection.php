<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\ResourceModel\Search\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Search\Model\ResourceModel\Query\Collection
{
    /**
     * @param mixed $connection
     * @param mixed $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Helper $resourceHelper,
        protected \Magento\Framework\Registry $_registryManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $resourceHelper,
            $connection,
            $resource
        );
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect(): static
    {
        parent::_initSelect();
        $queryId = $this->getQuery()->getId();
        if ($queryId) {
            $this->addFieldToFilter('query_id', ['nin' => $queryId]);
        }
        return $this;
    }

    /**
     *  Retrieve a value from registry by a key
     *
     * @return \Magento\Search\Model\Query
     */
    public function getQuery()
    {
        return $this->_registryManager->registry('current_catalog_search');
    }
}
