<?php

declare (strict_types=1);
/**
 * Application default url
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Default_Path;

class Default_Path implements \Magento\Framework\App\Default_Path_Interface
{
    /**
     * Default path parts
     *
     * @var array
     */
    protected $_parts;
    /**
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->_parts = $parts;
    }
    /**
     * Retrieve path part by key
     *
     * @param string $code
     * @return string
     */
    public function get_part($code)
    {
        return $this->_parts[$code] ?? null;
    }
}