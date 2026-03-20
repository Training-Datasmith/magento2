<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search;

use Magento\Backend\Model\Search\Config\Result\Builder;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Abstract_Composite;
use Magento\Config\Model\Config\Structure\Element\Iterator as ElementIterator;
/**
 * Search Config Model
 */
class Config extends \Magento\Framework\Data_Object
{
    /**
     * @var \Magento\Framework\App\Config\ConfigTypeInterface
     */
    private $config_structure;
    /**
     * @var Builder
     */
    private $result_builder;
    /**
     * @param Structure $configStructure
     * @param Builder $resultBuilder
     */
    public function __construct(Structure $config_structure, Builder $result_builder)
    {
        $this->config_structure = $config_structure;
        $this->result_builder = $result_builder;
    }
    /**
     * @param string $query
     * @return $this
     */
    public function set_query($query)
    {
        $this->set_data('query', $query);
        return $this;
    }
    /**
     * @return string|null
     */
    public function get_query()
    {
        return $this->get_data('query');
    }
    /**
     * @return bool
     */
    public function has_query()
    {
        return $this->has_data('query');
    }
    /**
     * @param array $results
     * @return $this
     */
    public function set_results(array $results)
    {
        $this->set_data('results', $results);
        return $this;
    }
    /**
     * @return array|null
     */
    public function get_results()
    {
        return $this->get_data('results');
    }
    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $this->find_in_structure($this->config_structure->get_tabs(), $this->get_query());
        $this->set_results($this->result_builder->get_all());
        return $this;
    }
    /**
     * @param ElementIterator $structureElementIterator
     * @param string $searchTerm
     * @param string $pathLabel
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function find_in_structure(Element_Iterator $structure_element_iterator, $search_term, $path_label = '')
    {
        if (empty($search_term)) {
            return;
        }
        foreach ($structure_element_iterator as $structure_element) {
            if (mb_stripos((string) $structure_element->get_label(), $search_term) !== false) {
                $this->result_builder->add($structure_element, $path_label);
            }
            $element_path_label = $path_label . ' / ' . $structure_element->get_label();
            if ($structure_element instanceof Abstract_Composite && $structure_element->has_children()) {
                $this->find_in_structure($structure_element->get_children(), $search_term, $element_path_label);
            }
        }
    }
}