<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * Universal data container with array access implementation
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
#[\Allow_Dynamic_Properties]
class Data_Object implements \ArrayAccess
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = [];
    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $_underscore_cache = [];
    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object attributes
     * This behavior may change in child classes
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }
    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return $this
     */
    public function add_data(array $arr)
    {
        if ($this->_data === []) {
            $this->set_data($arr);
            return $this;
        }
        foreach ($arr as $index => $value) {
            $this->set_data($index, $value);
        }
        return $this;
    }
    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array|null $key
     * @param mixed $value
     * @return $this
     */
    public function set_data($key, $value = null)
    {
        if ($key === (array) $key) {
            $this->_data = $key;
        } else {
            $key = $key ?? '';
            $this->_data[$key] = $value;
        }
        return $this;
    }
    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unset_data($key = null)
    {
        if ($key === null) {
            $this->set_data([]);
        } elseif (is_string($key)) {
            if (isset($this->_data[$key]) || array_key_exists($key, $this->_data)) {
                unset($this->_data[$key]);
            }
        } elseif ($key === (array) $key) {
            foreach ($key as $element) {
                $this->unset_data($element);
            }
        }
        return $this;
    }
    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_data($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->_data;
        }
        if ($key === null) {
            return null;
        }
        $data = $this->_data[$key] ?? null;
        if ($data === null && $key !== null && strpos($key, '/') !== false) {
            /* process a/b/c key as ['a']['b']['c'] */
            $data = $this->get_data_by_path($key);
        }
        if ($index !== null) {
            if ($data === (array) $data) {
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif ($data instanceof \Magento\Framework\Data_Object) {
                $data = $data->get_data($index);
            } else {
                $data = null;
            }
        }
        return $data;
    }
    /**
     * Get object data by path
     *
     * Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     *
     * @param string $path
     * @return mixed
     */
    public function get_data_by_path($path)
    {
        $keys = explode('/', (string) $path);
        $data = $this->_data;
        foreach ($keys as $key) {
            if ((array) $data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof \Magento\Framework\Data_Object) {
                $data = $data->get_data_by_key($key);
            } else {
                return null;
            }
        }
        return $data;
    }
    /**
     * Get object data by particular key
     *
     * @param string $key
     * @return mixed
     */
    public function get_data_by_key($key)
    {
        return $this->_get_data($key);
    }
    /**
     * Get value from _data array without parse key
     *
     * @param   string $key
     * @return  mixed
     */
    protected function _get_data($key)
    {
        $key = $key ?? '';
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }
    /**
     * Set object data with calling setter method
     *
     * @param string $key
     * @param mixed $args
     * @return $this
     */
    public function set_data_using_method($key, $args = [])
    {
        $method = 'set' . ($key !== null ? str_replace('_', '', ucwords($key, '_')) : '');
        $this->{$method}($args);
        return $this;
    }
    /**
     * Get object data by key with calling getter method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function get_data_using_method($key, $args = null)
    {
        $method = 'get' . ($key !== null ? str_replace('_', '', ucwords($key, '_')) : '');
        return $this->{$method}($args);
    }
    /**
     * If $key is empty, checks whether there's any data in the object
     *
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function has_data($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }
        return array_key_exists($key, $this->_data);
    }
    /**
     * Convert array of object data with to array with keys requested in $keys array
     *
     * @param array $keys array of required keys
     * @return array
     */
    public function to_array(array $keys = [])
    {
        if (empty($keys)) {
            return $this->_data;
        }
        $result = [];
        foreach ($keys as $key) {
            if (isset($this->_data[$key])) {
                $result[$key] = $this->_data[$key];
            } else {
                $result[$key] = null;
            }
        }
        return $result;
    }
    /**
     * The "__" style wrapper for toArray method
     *
     * @param  array $keys
     * @return array
     */
    public function convert_to_array(array $keys = [])
    {
        return $this->to_array($keys);
    }
    /**
     * Convert object data into XML string
     *
     * @param array $keys array of keys that must be represented
     * @param string $rootName root node name
     * @param bool $addOpenTag flag that allow to add initial xml node
     * @param bool $addCdata flag that require wrap all values in CDATA
     * @return string
     */
    public function to_xml(array $keys = [], $root_name = 'item', $add_open_tag = false, $add_cdata = true)
    {
        $xml = '';
        $data = $this->to_array($keys);
        foreach ($data as $field_name => $field_value) {
            if ($add_cdata === true) {
                $field_value = "<![CDATA[{$field_value}]]>";
            } else {
                $field_value = $field_value !== null ? str_replace(['&', '"', "'", '<', '>'], ['&amp;', '&quot;', '&apos;', '&lt;', '&gt;'], $field_value) : '';
            }
            $xml .= "<{$field_name}>{$field_value}</{$field_name}>\n";
        }
        if ($root_name) {
            $xml = "<{$root_name}>\n{$xml}</{$root_name}>\n";
        }
        if ($add_open_tag) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }
        return $xml;
    }
    /**
     * The "__" style wrapper for toXml method
     *
     * @param array $arrAttributes array of keys that must be represented
     * @param string $rootName root node name
     * @param bool $addOpenTag flag that allow to add initial xml node
     * @param bool $addCdata flag that require wrap all values in CDATA
     * @return string
     */
    public function convert_to_xml(array $arr_attributes = [], $root_name = 'item', $add_open_tag = false, $add_cdata = true)
    {
        return $this->to_xml($arr_attributes, $root_name, $add_open_tag, $add_cdata);
    }
    /**
     * Convert object data to JSON
     *
     * @param array $keys array of required keys
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function to_json(array $keys = [])
    {
        $data = $this->to_array($keys);
        return \Magento\Framework\Serialize\Json_Converter::convert($data);
    }
    /**
     * The "__" style wrapper for toJson
     *
     * @param array $keys
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function convert_to_json(array $keys = [])
    {
        return $this->to_json($keys);
    }
    /**
     * Convert object data into string with predefined format
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format
     * @return string
     */
    public function to_string($format = '')
    {
        if (empty($format)) {
            $result = implode(', ', $this->get_data());
        } else {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach ($matches[1] as $var) {
                $data = $this->get_data($var) ?? '';
                $format = str_replace('{{' . $var . '}}', $data, $format);
            }
            $result = $format;
        }
        return $result;
    }
    /**
     * Set/Get attribute wrapper
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __call($method, $args)
    {
        // Compare 3 first letters of the method name
        switch ($method[0] . ($method[1] ?? '') . ($method[2] ?? '')) {
            case 'get':
                if (isset($args[0]) && $args[0] !== null) {
                    return $this->get_data(self::$_underscore_cache[$method] ?? $this->_underscore($method), $args[0]);
                }
                return $this->get_data(self::$_underscore_cache[$method] ?? $this->_underscore($method), $args[0] ?? null);
            case 'set':
                return $this->set_data(self::$_underscore_cache[$method] ?? $this->_underscore($method), $args[0] ?? null);
            case 'uns':
                return $this->unset_data(self::$_underscore_cache[$method] ?? $this->_underscore($method));
            case 'has':
                return isset($this->_data[self::$_underscore_cache[$method] ?? $this->_underscore($method)]);
        }
        throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Invalid method %1::%2', [get_class($this), $method]));
    }
    /**
     * Checks whether the object is empty
     *
     * @return bool
     */
    public function is_empty()
    {
        if (empty($this->_data)) {
            return true;
        }
        return false;
    }
    /**
     * Converts field names for setters and getters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscore_cache[$name])) {
            return self::$_underscore_cache[$name];
        }
        $result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', '_$1', lcfirst(substr($name, 3))), '_'));
        self::$_underscore_cache[$name] = $result;
        return $result;
    }
    /**
     * Convert object data into string with defined keys and values.
     *
     * Example: key1="value1" key2="value2" ...
     *
     * @param   array $keys array of accepted keys
     * @param   string $valueSeparator separator between key and value
     * @param   string $fieldSeparator separator between key/value pairs
     * @param   string $quote quoting sign
     * @return  string
     */
    public function serialize($keys = [], $value_separator = '=', $field_separator = ' ', $quote = '"')
    {
        $data = [];
        if (empty($keys)) {
            $keys = array_keys($this->_data);
        }
        foreach ($this->_data as $key => $value) {
            if (in_array($key, $keys)) {
                $data[] = $key . $value_separator . $quote . $value . $quote;
            }
        }
        $res = implode($field_separator, $data);
        return $res;
    }
    /**
     * Present object data as string in debug mode
     *
     * @param mixed $data
     * @param array $objects
     * @return array
     */
    public function debug($data = null, &$objects = [])
    {
        if ($data === null) {
            $hash = spl_object_hash($this);
            if (!empty($objects[$hash])) {
                return '*** RECURSION ***';
            }
            $objects[$hash] = true;
            $data = $this->get_data();
        }
        $debug = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $debug[$key] = $value;
            } elseif (is_array($value)) {
                $debug[$key] = $this->debug($value, $objects);
            } elseif ($value instanceof \Magento\Framework\Data_Object) {
                $debug[$key . ' (' . get_class($value) . ')'] = $value->debug(null, $objects);
            }
        }
        return $debug;
    }
    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    #[\Return_Type_Will_Change]
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }
    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    #[\Return_Type_Will_Change]
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]) || array_key_exists($offset, $this->_data);
    }
    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    #[\Return_Type_Will_Change]
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }
    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    #[\Return_Type_Will_Change]
    public function offsetGet($offset)
    {
        if (isset($this->_data[$offset])) {
            return $this->_data[$offset];
        }
        return null;
    }
    /**
     * Export only scalar and arrays properties for var_dump
     *
     * @return array
     */
    public function __debugInfo()
    {
        return array_filter($this->_data, function ($v) {
            return is_scalar($v) || is_array($v);
        });
    }
}