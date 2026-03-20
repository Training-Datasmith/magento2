<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache\Config;

use Magento\Framework\App\Area;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\File_Resolver_Interface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\Validation_State_Interface;
/**
 * Cache configuration reader
 */
class Reader extends Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = ['/config/type' => 'name'];
    /**
     * Initialize dependencies.
     *
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(File_Resolver_Interface $file_resolver, Converter $converter, Schema_Locator $schema_locator, Validation_State_Interface $validation_state, $file_name = 'cache.xml', $id_attributes = [], $dom_document_class = Dom::class, $default_scope = Area::AREA_GLOBAL)
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
}