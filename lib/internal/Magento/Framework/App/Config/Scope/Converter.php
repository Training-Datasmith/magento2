<?php

declare (strict_types=1);
/**
 * Configuration data converter. Converts associative array to tree array
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Scope;

class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Convert config data
     *
     * @param array $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        foreach ($source as $key => $value) {
            $this->_set_array_value($output, $key, $value);
        }
        return $output;
    }
    /**
     * Set array value by path
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     */
    protected function _set_array_value(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $current_pointer =& $container;
        foreach ($segments as $segment) {
            if (!isset($current_pointer[$segment])) {
                $current_pointer[$segment] = [];
            }
            $current_pointer =& $current_pointer[$segment];
        }
        $current_pointer = $value;
    }
}