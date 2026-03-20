<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\Header_Provider;

use Magento\Framework\HTTP\Header;
class Xss_Protection extends Abstract_Header_Provider
{
    /**
     * @var string
     */
    protected $header_name = 'X-XSS-Protection';
    /** Matches IE 8 browsers */
    public const IE_8_USER_AGENT = 'MSIE 8';
    /** Value for browsers except IE 8 */
    public const HEADER_ENABLED = '1; mode=block';
    /** Value for IE 8 */
    public const HEADER_DISABLED = '0';
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $header_service;
    /**
     * @param Header $headerService
     */
    public function __construct(Header $header_service)
    {
        $this->header_service = $header_service;
    }
    /**
     * Header value. Must be disabled for IE 8.
     *
     * @return string
     */
    public function get_value()
    {
        return strpos($this->header_service->get_http_user_agent(), self::IE_8_USER_AGENT) === false ? self::HEADER_ENABLED : self::HEADER_DISABLED;
    }
}