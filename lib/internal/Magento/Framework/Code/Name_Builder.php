<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code;

/**
 * Builds namespace with classname out of the parts.
 *
 * @api
 * @since 100.0.2
 */
class Name_Builder
{
    /**
     * Builds namespace + classname out of the parts array
     *
     * Split every part into pieces by _ and \ and uppercase every piece
     * Then join them back using \
     *
     * @param string[] $parts
     * @return string
     */
    public function build_class_name($parts)
    {
        $separator = '\\';
        $string = join($separator, $parts);
        $string = str_replace('_', $separator, $string);
        $class_name = ucwords($string, $separator);
        return $class_name;
    }
}