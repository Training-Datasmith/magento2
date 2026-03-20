<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Encryption;

/**
 * @api
 * @since 100.0.2
 */
class Url_Coder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(\Magento\Framework\Url_Interface $url)
    {
        $this->_url = $url;
    }
    /**
     * The base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     */
    public function encode($url)
    {
        return strtr(base64_encode((string) $url), '+/=', '-_,');
    }
    /**
     *  The base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     */
    public function decode($url)
    {
        return $this->_url->session_url_var(base64_decode(strtr((string) $url, '-_~', '+/=')));
    }
}