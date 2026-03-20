<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\App\ScopeValidatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ScopeValidator implements ScopeValidatorInterface
{
    /**
     * @var ScopeResolverPool
     */
    protected $scopeResolverPool;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * @inheritDoc
     */
    public function isValidScope($scope, $scopeId = null)
    {
        if ($scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT && !$scopeId) {
            return true;
        }

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            if (!$scopeResolver->getScope($scopeId)->getId()) {
                return false;
            }
        } catch (\InvalidArgumentException $e) {
            return false;
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return true;
    }
}
