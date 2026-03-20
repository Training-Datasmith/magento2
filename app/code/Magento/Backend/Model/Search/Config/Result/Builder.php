<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search\Config\Result;

use Magento\Backend\Model\Search\Config\Structure\Element_Builder_Interface;
use Magento\Backend\Model\Url_Interface;
use Magento\Config\Model\Config\Structure_Element_Interface;
/**
 * Config SearchResult Builder
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Builder
{
    /**
     * @var array
     */
    private $results = [];
    /**
     * @var UrlInterface
     */
    private $url_builder;
    /**
     * @var ElementBuilderInterface[]
     */
    private $structure_element_types;
    /**
     * @param UrlInterface $urlBuilder
     * @param array $structureElementTypes
     */
    public function __construct(Url_Interface $url_builder, array $structure_element_types)
    {
        $this->url_builder = $url_builder;
        $this->structure_element_types = $structure_element_types;
    }
    /**
     * @return array
     */
    public function get_all()
    {
        return $this->results;
    }
    /**
     * @param StructureElementInterface $structureElement
     * @param string $elementPathLabel
     * @return void
     */
    public function add(Structure_Element_Interface $structure_element, $element_path_label)
    {
        $url_params = [];
        $element_data = $structure_element->get_data();
        if (!in_array($element_data['_elementType'], array_keys($this->structure_element_types))) {
            return;
        }
        if (isset($this->structure_element_types[$element_data['_elementType']])) {
            $url_params_builder = $this->structure_element_types[$element_data['_elementType']];
            $url_params = $url_params_builder->build($structure_element);
        }
        $this->results[] = ['id' => $structure_element->get_path(), 'type' => null, 'name' => (string) $structure_element->get_label(), 'description' => $element_path_label, 'url' => $this->url_builder->get_url('*/system_config/edit', $url_params)];
    }
}