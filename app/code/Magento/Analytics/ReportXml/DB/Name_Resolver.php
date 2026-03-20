<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

/**
 * Resolver for source names
 */
class Name_Resolver
{
    /**
     * Returns element for name
     *
     * @return string
     */
    public function get_name(array $element_config)
    {
        return $element_config['name'];
    }
    /**
     * Returns alias
     *
     * @return string
     */
    public function get_alias(array $element_config)
    {
        return $element_config['alias'] ?? $this->get_name($element_config);
    }
}