<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Design Interface
 *
 * @api
 */
interface Design_Interface
{
    /**
     * Load custom design settings for specified store and date
     *
     * @param string $storeId
     * @param string|null $date
     * @return $this
     */
    public function load_change($store_id, $date = null);
    /**
     * Apply design change from self data into specified design package instance
     *
     * @param \Magento\Framework\View\DesignInterface $packageInto
     * @return $this
     */
    public function change_design(\Magento\Framework\View\Design_Interface $package_into);
}