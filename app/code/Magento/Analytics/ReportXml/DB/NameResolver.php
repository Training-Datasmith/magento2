<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

/**
 * Resolver for source names
 */
class NameResolver
{
    /**
     * Returns element for name
     *
     * @return string
     */
    public function getName(array $elementConfig)
    {
        return $elementConfig['name'];
    }

    /**
     * Returns alias
     *
     * @return string
     */
    public function getAlias(array $elementConfig)
    {
        return $elementConfig['alias'] ?? $this->getName($elementConfig);
    }
}
