<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\Source;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Class StockConfiguration
 */
class StockConfiguration implements ValueSourceInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        $value = $this->stockConfiguration->getDefaultConfigValue($name);
        return is_numeric($value) ? (float)$value : $value;
    }
}
