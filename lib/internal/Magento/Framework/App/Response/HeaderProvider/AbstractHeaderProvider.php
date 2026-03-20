<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\Header_Provider;

/**
 * Class to be used for setting headers with static values
 */
abstract class Abstract_Header_Provider implements \Magento\Framework\App\Response\Header_Provider\Header_Provider_Interface
{
    /**
     * @var string
     */
    protected $header_name = '';
    /**
     * @var string
     */
    protected $header_value = '';
    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function can_apply()
    {
        return true;
    }
    /**
     * Get header name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->header_name;
    }
    /**
     * Get header value
     *
     * @return string
     */
    public function get_value()
    {
        return $this->header_value;
    }
}