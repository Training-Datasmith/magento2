<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\Header_Provider;

use Magento\Framework\App\Response\Http;
/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class X_Frame_Options extends \Magento\Framework\App\Response\Header_Provider\Abstract_Header_Provider
{
    /** Deployment config key for frontend x-frame-options header value */
    public const DEPLOYMENT_CONFIG_X_FRAME_OPT = 'x-frame-options';
    /** Always send SAMEORIGIN in backend x-frame-options header */
    public const BACKEND_X_FRAME_OPT = 'SAMEORIGIN';
    /**
     * x-frame-options Header name
     *
     * @var string
     */
    protected $header_name = Http::HEADER_X_FRAME_OPT;
    /**
     * x-frame-options header value
     *
     * @var string
     */
    protected $header_value;
    /**
     * @param string $xFrameOpt
     */
    public function __construct($x_frame_opt = 'SAMEORIGIN')
    {
        $this->header_value = $x_frame_opt;
    }
}