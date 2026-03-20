<?php

declare (strict_types=1);
/**
 * Resources configuration filesystem loader
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = ['/config/resource' => 'name'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, Converter $converter, Schema_Locator $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'resources.xml', $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
    /**
     * Read resource configuration
     *
     * @param string $scope
     * @return array
     */
    public function read($scope = null)
    {
        return $scope !== 'primary' ? parent::read($scope) : [];
    }
}