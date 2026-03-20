<?php

declare (strict_types=1);
/**
 * Event observers configuration filesystem loader. Loads event observers configuration from XML files, split by scopes
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = ['/config/event' => 'name', '/config/event/observer' => 'name'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Event\Config\Converter $converter
     * @param \Magento\Framework\Event\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Event\Config\Converter $converter, \Magento\Framework\Event\Config\Schema_Locator $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'events.xml', $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
}