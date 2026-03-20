<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Rss;

/**
 * Interface UrlBuilderInterface
 *
 * @api
 */
interface Url_Builder_Interface
{
    /**
     * @param array $queryParams
     * @return mixed
     */
    public function get_url(array $query_params = []);
}