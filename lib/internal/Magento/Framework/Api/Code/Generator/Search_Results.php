<?php

declare (strict_types=1);
/**
 * @category    Magento
 * @package     Magento_Code
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\Entity_Abstract;
/**
 * Class Builder
 */
class Search_Results extends Entity_Abstract
{
    /**
     * Entity type
     */
    public const ENTITY_TYPE = 'searchResults';
    /**
     * Search result default class
     * @deprecated
     */
    public const SEARCH_RESULT = '\\' . \Magento\Framework\Api\Search_Results::class;
    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _get_class_properties()
    {
        return [];
    }
    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _get_class_methods()
    {
        $get_items = ['name' => 'getItems', 'parameters' => [], 'body' => 'return parent::getItems();', 'docblock' => ['shortDescription' => 'Returns array of items', 'tags' => [['name' => 'return', 'description' => $this->get_source_class_name() . '[]']]]];
        return [$get_items];
    }
    /**
     * Returns default constructor definition
     *
     * @return array
     */
    protected function _get_default_constructor_definition()
    {
        return [];
    }
    /**
     * Generate code
     *
     * @return string
     */
    protected function _generate_code()
    {
        $this->_class_generator->set_name($this->_get_result_class_name())->set_extended_class('\\' . \Magento\Framework\Api\Search_Results::class)->add_methods($this->_get_class_methods());
        return $this->_get_generated_code();
    }
}