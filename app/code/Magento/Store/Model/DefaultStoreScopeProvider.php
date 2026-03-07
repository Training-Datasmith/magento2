<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Model;

use Magento\Framework\Model\Entity\ScopeFactory;
use Magento\Framework\Model\Entity\ScopeProviderInterface;

/**
 * Class StoreScope
 */
class DefaultStoreScopeProvider implements ScopeProviderInterface
{
    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    /**
     * StoreScopeProvider constructor.
     *
     * @param ScopeFactory $scopeFactory
     */
    public function __construct(
        ScopeFactory $scopeFactory
    ) {
        $this->scopeFactory = $scopeFactory;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return \Magento\Framework\Model\Entity\ScopeInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContext($entityType, $entityData = [])
    {
        return $this->scopeFactory->create(Store::STORE_ID, Store::DEFAULT_STORE_ID, null);
    }
}
