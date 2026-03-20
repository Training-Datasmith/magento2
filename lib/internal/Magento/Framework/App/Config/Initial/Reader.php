<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Initial;

/**
 * Default configuration data reader. Reads configuration data from storage
 */
class Reader
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $_file_resolver;
    /**
     * Config converter
     *
     * @var  \Magento\Framework\Config\ConverterInterface
     */
    protected $_converter;
    /**
     * Config file name
     *
     * @var string
     */
    protected $_file_name;
    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $_dom_document_class;
    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scope_priority_scheme = ['global'];
    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     */
    protected $_schema_file;
    /**
     * @var \Magento\Framework\Config\DomFactory
     */
    private $dom_factory;
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @param string $fileName
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Config\Converter_Interface $converter, Schema_Locator $schema_locator, \Magento\Framework\Config\Dom_Factory $dom_factory, $file_name = 'config.xml')
    {
        $this->_schema_file = $schema_locator->get_schema();
        $this->_file_resolver = $file_resolver;
        $this->_converter = $converter;
        $this->dom_factory = $dom_factory;
        $this->_file_name = $file_name;
    }
    /**
     * Read configuration scope
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function read()
    {
        $file_list = [];
        foreach ($this->_scope_priority_scheme as $scope) {
            $directories = $this->_file_resolver->get($this->_file_name, $scope);
            foreach ($directories as $key => $directory) {
                $file_list[$key] = $directory;
            }
        }
        if (!count($file_list)) {
            return [];
        }
        /** @var \Magento\Framework\Config\Dom $domDocument */
        $dom_document = null;
        foreach ($file_list as $file) {
            try {
                if (!$dom_document) {
                    $dom_document = $this->dom_factory->create_dom(['xml' => $file, 'schemaFile' => $this->_schema_file]);
                } else {
                    $dom_document->merge($file);
                }
            } catch (\Magento\Framework\Config\Dom\Validation_Exception $e) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.", [$file, $e->get_message()]));
            }
        }
        $output = [];
        if ($dom_document) {
            $output = $this->_converter->convert($dom_document->get_dom());
        }
        return $output;
    }
}