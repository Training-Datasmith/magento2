<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Convert;

/**
 * Magento data size converter class
 */
class Data_Size
{
    /**
     * Converts a size value to bytes
     *
     * Example input: 100 (bytes), 10K (kilobytes), 13M (megabytes), 2G (gigabytes)
     *
     * @param string $size
     * @return integer
     */
    public function convert_size_to_bytes($size)
    {
        if ($size && !is_numeric($size)) {
            $type = strtoupper(substr($size, -1));
            $size = (int) $size;
            switch ($type) {
                case 'K':
                    $size *= 1024;
                    break;
                case 'M':
                    $size *= 1024 * 1024;
                    break;
                case 'G':
                    $size *= 1024 * 1024 * 1024;
                    break;
                default:
                    break;
            }
        }
        return (int) $size;
    }
}