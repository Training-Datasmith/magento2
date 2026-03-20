<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\Header_Provider;

class X_Content_Type_Options extends Abstract_Header_Provider
{
    /**
     * @var string
     */
    protected $header_value = 'nosniff';
    /**
     * @var string
     */
    protected $header_name = 'X-Content-Type-Options';
}