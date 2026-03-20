<?php

declare (strict_types=1);
/**
 * Root ACL Resource
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl;

/**
 * @api
 * @since 100.0.2
 */
class Root_Resource
{
    /**
     * Root resource id
     *
     * @var string
     */
    protected $_identifier;
    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->_identifier = $identifier;
    }
    /**
     * Retrieve root resource id
     *
     * @return string
     */
    public function get_id()
    {
        return $this->_identifier;
    }
}