<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Page_Cache;

/**
 * Cache model for builtin cache
 *
 * @deprecated 100.1.0
 */
class Cache extends \Magento\Framework\App\Cache
{
    /**
     * @var string
     *
     * @deprecated 100.1.0
     */
    protected $_frontend_identifier = 'page_cache';
}