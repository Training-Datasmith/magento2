<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object;

/**
 * Utility class for mapping data between objects or arrays
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Mapper
{
    /**
     * Convert data from source to target item using map array
     *
     * Will get or set data with generic or magic, or specified Magento Object methods, or with array keys
     * from or to \Magento\Framework\DataObject or array
     * :)
     *
     * Map must either be associative array of keys from=>to
     * or a numeric array of keys, assuming from = to
     *
     * Defaults must be assoc array of keys => values. Target will get default, if the value is not present in source
     * If the source has getter defined instead of magic method, the value will be taken only if not empty
     *
     * Callbacks explanation (when $from or $to is not array):
     *   for $from:
     *     <\Magento\Framework\DataObject> => $from->getData($key) (default)
     *     array(<\Magento\Framework\DataObject>, <method>) => $from->$method($key)
     *   for $to (makes sense only for \Magento\Framework\DataObject):
     *     <\Magento\Framework\DataObject> => $from->setData($key, <from>)
     *     array(<\Magento\Framework\DataObject>, <method>) => $from->$method($key, <from>)
     *
     * @param array|\Magento\Framework\DataObject|callable $from
     * @param array|\Magento\Framework\DataObject|callable $to
     * @param array $map
     * @param array $defaults
     * @return array|object
     */
    public static function &accumulate_by_map($from, $to, array $map, array $defaults = [])
    {
        $get = 'getData';
        if (is_array($from) && isset($from[0]) && is_object($from[0]) && isset($from[1]) && is_string($from[1]) && is_callable($from)) {
            list($from, $get) = $from;
        }
        $from_is_array = is_array($from);
        $from_is_vo = $from instanceof \Magento\Framework\Data_Object;
        $set = 'setData';
        if (is_array($to) && isset($to[0]) && is_object($to[0]) && isset($to[1]) && is_string($to[1]) && is_callable($to)) {
            list($to, $set) = $to;
        }
        $to_is_array = is_array($to);
        $to_is_vo = $to instanceof \Magento\Framework\Data_Object;
        foreach ($map as $key_from => $key_to) {
            if (!is_string($key_from)) {
                $key_from = $key_to;
            }
            if ($from_is_array) {
                if (array_key_exists($key_from, $from)) {
                    if ($to_is_array) {
                        $to[$key_to] = $from[$key_from];
                    } elseif ($to_is_vo) {
                        $to->{$set}($key_to, $from[$key_from]);
                    }
                }
            } elseif ($from_is_vo) {
                // get value if (any) value is found as in magic data or a non-empty value with declared getter
                $value = null;
                if ($should_get = $from->has_data($key_from)) {
                    $value = $from->{$get}($key_from);
                } elseif (method_exists($from, $get)) {
                    $value = $from->{$get}($key_from);
                    if ($value) {
                        $should_get = true;
                    }
                }
                if ($should_get) {
                    if ($to_is_array) {
                        $to[$key_to] = $value;
                    } elseif ($to_is_vo) {
                        $to->{$set}($key_to, $value);
                    }
                }
            }
        }
        foreach ($defaults as $key_to => $value) {
            if ($to_is_array) {
                if (!isset($to[$key_to])) {
                    $to[$key_to] = $value;
                }
            } elseif ($to_is_vo) {
                if (!$to->has_data($key_to)) {
                    $to->{$set}($key_to, $value);
                }
            }
        }
        return $to;
    }
}