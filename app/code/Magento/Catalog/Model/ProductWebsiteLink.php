<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model;

class ProductWebsiteLink extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface
{
    /**#@+
     * Field names
     */
    public const KEY_SKU = 'sku';
    public const WEBSITE_ID = 'website_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->_get(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }
}
