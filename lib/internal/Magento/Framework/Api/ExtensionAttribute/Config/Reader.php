<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = ['/config/extension_attributes' => 'for', '/config/extension_attributes/attribute' => 'code'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Api\ExtensionAttribute\Config\Converter $converter
     * @param \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Api\Extension_Attribute\Config\Converter $converter, \Magento\Framework\Api\Extension_Attribute\Config\Schema_Locator $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'extension_attributes.xml', $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
}