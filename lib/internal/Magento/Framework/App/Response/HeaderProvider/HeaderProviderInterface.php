<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Response\Header_Provider;

/**
 * Interface \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface
 *
 * @api
 */
interface Header_Provider_Interface
{
    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function can_apply();
    /**
     * Header name
     *
     * @return string
     */
    public function get_name();
    /**
     * Header value
     *
     * @return string
     */
    public function get_value();
}