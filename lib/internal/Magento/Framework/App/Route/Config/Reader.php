<?php

declare (strict_types=1);
/**
 * Routes configuration reader
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of paths to identifiable nodes
     *
     * @var array
     */
    protected $_id_attributes = ['/config/router' => 'id', '/config/router/route' => 'id', '/config/router/route/module' => 'name'];
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, Converter $converter, Schema_Locator $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name = 'routes.xml')
    {
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $this->_id_attributes);
    }
}