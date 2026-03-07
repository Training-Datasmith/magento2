<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\Model\Entity\ScopeInterface;

/**
 * Class ReadSnapshot
 */
class ReadSnapshot extends ReadHandler
{
    /**
     * @param ScopeInterface $scope
     * @return array
     */
    protected function getContextVariables(ScopeInterface $scope)
    {
        $data[] = $scope->getValue();
        return $data;
    }
}
