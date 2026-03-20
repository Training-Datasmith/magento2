<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

/**
 * Class ScalarTypesProvider returns array of supported scalar types.
 */
class Scalar_Types_Provider
{
    /**
     * Return array of scalar types.
     *
     * @return array
     */
    public function get_types()
    {
        return ['array', 'string', 'int', 'integer', 'float', 'bool', 'boolean', 'mixed', 'callable'];
    }
}