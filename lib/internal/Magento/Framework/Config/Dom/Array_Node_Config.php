<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of nodes that represent numeric or associative arrays
 */
class Array_Node_Config
{
    /**
     * @var NodePathMatcher
     */
    private $node_path_matcher;
    /**
     * Format: array('/associative/array/path' => '<array_key_attribute>', ...)
     *
     * @var array
     */
    private $assoc_arrays = [];
    /**
     * Format: array('/numeric/array/path', ...)
     *
     * @var array
     */
    private $numeric_arrays = [];
    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $assocArrayAttributes
     * @param array $numericArrays
     */
    public function __construct(Node_Path_Matcher $node_path_matcher, array $assoc_array_attributes, array $numeric_arrays = [])
    {
        $this->node_path_matcher = $node_path_matcher;
        $this->assoc_arrays = $assoc_array_attributes;
        $this->numeric_arrays = $numeric_arrays;
    }
    /**
     * Whether a node is a numeric array or not
     *
     * @param string $nodeXpath
     * @return bool
     */
    public function is_numeric_array($node_xpath)
    {
        foreach ($this->numeric_arrays as $path_pattern) {
            if ($this->node_path_matcher->match($path_pattern, $node_xpath)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Retrieve name of array key attribute, if a node is an associative array
     *
     * @param string $nodeXpath
     * @return string|null
     */
    public function get_assoc_array_key_attribute($node_xpath)
    {
        foreach ($this->assoc_arrays as $path_pattern => $key_attribute) {
            if ($this->node_path_matcher->match($path_pattern, $node_xpath)) {
                return $key_attribute;
            }
        }
        return null;
    }
}