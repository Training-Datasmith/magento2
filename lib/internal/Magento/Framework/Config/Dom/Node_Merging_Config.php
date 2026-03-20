<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of identifier attributes to be taken into account during merging
 */
class Node_Merging_Config
{
    /**
     * @var NodePathMatcher
     */
    private $node_path_matcher;
    /**
     * Format: array('/node/path' => '<node_id_attribute>', ...)
     *
     * @var array
     */
    private $id_attributes = [];
    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $idAttributes
     */
    public function __construct(Node_Path_Matcher $node_path_matcher, array $id_attributes)
    {
        $this->node_path_matcher = $node_path_matcher;
        $this->id_attributes = $id_attributes;
    }
    /**
     * Retrieve name of an identifier attribute for a node
     *
     * @param string $nodeXpath
     * @return string|null
     */
    public function get_id_attribute($node_xpath)
    {
        foreach ($this->id_attributes as $path_pattern => $id_attribute) {
            if ($this->node_path_matcher->match($path_pattern, $node_xpath)) {
                return $id_attribute;
            }
        }
        return null;
    }
}