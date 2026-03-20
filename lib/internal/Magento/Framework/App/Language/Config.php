<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Language;

use Magento\Framework\Config\Dom;
/**
 * Language pack configuration file
 */
class Config
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urn_resolver;
    /**
     * @var \Magento\Framework\Config\DomFactory
     */
    protected $dom_factory;
    /**
     * Data extracted from the configuration file
     *
     * @var array
     */
    protected $_data;
    /**
     * Constructor
     *
     * @param string $source
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct($source, \Magento\Framework\Config\Dom\Urn_Resolver $urn_resolver, \Magento\Framework\Config\Dom_Factory $dom_factory)
    {
        $this->urn_resolver = $urn_resolver;
        $this->dom_factory = $dom_factory;
        $dom = $this->dom_factory->create_dom(['xml' => $source, 'schemaFile' => $this->get_schema_file()]);
        $this->_data = $this->_extract_data($dom->get_dom());
    }
    /**
     * Get absolute path to validation scheme for language.xml
     *
     * @return string
     */
    protected function get_schema_file()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:App/Language/package.xsd');
    }
    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extract_data(\Dom_Document $dom)
    {
        /** @var $languageNode \DOMElement */
        $language_node = $dom->get_elements_by_tag_name('language')->item(0);
        /** @var $codeNode \DOMElement */
        $code_node = $language_node->get_elements_by_tag_name('code')->item(0);
        /** @var $vendorNode \DOMElement */
        $vendor_node = $language_node->get_elements_by_tag_name('vendor')->item(0);
        /** @var $packageNode \DOMElement */
        $package_node = $language_node->get_elements_by_tag_name('package')->item(0);
        /** @var $sortOrderNode \DOMElement */
        $sort_order_node = $language_node->get_elements_by_tag_name('sort_order')->item(0);
        $use = [];
        /** @var $useNode \DOMElement */
        foreach ($language_node->get_elements_by_tag_name('use') as $use_node) {
            $use[] = ['vendor' => $use_node->get_attribute('vendor'), 'package' => $use_node->get_attribute('package')];
        }
        return ['code' => $code_node->node_value, 'vendor' => $vendor_node->node_value, 'package' => $package_node->node_value, 'sort_order' => $sort_order_node ? $sort_order_node->node_value : 0, 'use' => $use];
    }
    /**
     * Language code
     *
     * @return string
     */
    public function get_code()
    {
        return $this->_data['code'];
    }
    /**
     * Language vendor
     *
     * @return string
     */
    public function get_vendor()
    {
        return $this->_data['vendor'];
    }
    /**
     * Language package
     *
     * @return string
     */
    public function get_package()
    {
        return $this->_data['package'];
    }
    /**
     * Sort order
     *
     * @return null|int
     */
    public function get_sort_order()
    {
        return $this->_data['sort_order'];
    }
    /**
     * Declaration of Inheritances
     *
     * @return string[][]
     */
    public function get_uses()
    {
        return $this->_data['use'];
    }
}