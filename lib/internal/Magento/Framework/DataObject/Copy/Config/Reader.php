<?php

declare (strict_types=1);
/**
 * Fieldset configuration reader
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object\Copy\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_id_attributes = ['/config/scope' => 'id', '/config/scope/fieldset' => 'id', '/config/scope/fieldset/field' => 'name', '/config/scope/fieldset/field/aspect' => 'name'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\DataObject\Copy\Config\Converter $converter
     * @param \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Data_Object\Copy\Config\Converter $converter, \Magento\Framework\Config\Schema_Locator_Interface $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'fieldset.xml', $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
}