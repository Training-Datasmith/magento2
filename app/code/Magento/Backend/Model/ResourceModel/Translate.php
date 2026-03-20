<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Resource_Model;

/**
 * Backend translate resource model
 * @api
 * @since 100.0.2
 */
class Translate extends \Magento\Translation\Model\Resource_Model\Translate
{
    /**
     * Get current store id
     * Use always default scope for store id
     *
     * @return int
     */
    protected function _get_store_id()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}