<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource\Config\Reader;

class Filesystem extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = ['/config/acl/resources(/resource)+' => 'id'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param \Magento\Framework\Acl\AclResource\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Config\Converter_Interface $converter, \Magento\Framework\Acl\Acl_Resource\Config\Schema_Locator $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'acl.xml', $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
}