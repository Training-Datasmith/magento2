<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * View configuration files handler
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @var array
     */
    protected $xpath;
    /**
     * View config data
     *
     * @var array
     */
    protected $data;
    /**
     * @param FileResolverInterface $fileResolver
     * @param ConverterInterface $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @param array $xpath
     */
    public function __construct(File_Resolver_Interface $file_resolver, Converter_Interface $converter, Schema_Locator_Interface $schema_locator, Validation_State_Interface $validation_state, $file_name, $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global', $xpath = [])
    {
        $this->xpath = $xpath;
        $id_attributes = $this->get_id_attributes();
        parent::__construct($file_resolver, $converter, $schema_locator, $validation_state, $file_name, $id_attributes, $dom_document_class, $default_scope);
    }
    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     */
    public function get_vars($module)
    {
        $this->init_data();
        return $this->data['vars'][$module] ?? [];
    }
    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false|array
     */
    public function get_var_value($module, $var)
    {
        $this->init_data();
        if (!isset($this->data['vars'][$module])) {
            return false;
        }
        $value = $this->data['vars'][$module];
        foreach (explode('/', $var ?: '') as $node) {
            if (is_array($value) && isset($value[$node])) {
                $value = $value[$node];
            } else {
                return false;
            }
        }
        return $value;
    }
    /**
     * Retrieve a list media attributes in scope of specified module
     *
     * @param string $module
     * @param string $mediaType
     * @return array
     */
    public function get_media_entities($module, $media_type)
    {
        $this->init_data();
        return $this->data['media'][$module][$media_type] ?? [];
    }
    /**
     * Retrieve array of media attributes
     *
     * @param string $module
     * @param string $mediaType
     * @param string $mediaId
     * @return array
     */
    public function get_media_attributes($module, $media_type, $media_id)
    {
        $this->init_data();
        return $this->data['media'][$module][$media_type][$media_id] ?? [];
    }
    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function get_id_attributes()
    {
        $id_attributes = ['/view/vars' => 'module', '/view/vars/(var/)*var' => 'name', '/view/exclude/item' => ['type', 'item']];
        foreach ($this->xpath as $attribute) {
            if (is_array($attribute)) {
                foreach ($attribute as $key => $id) {
                    if (count($id) > 1) {
                        $id_attributes[$key] = array_values($id);
                    } else {
                        $id_attributes[$key] = array_shift($id);
                    }
                }
            }
        }
        return $id_attributes;
    }
    /**
     * Get excluded file list
     *
     * @return array
     */
    public function get_excluded_files()
    {
        $items = $this->get_items();
        return $items['file'] ?? [];
    }
    /**
     * Get excluded directory list
     *
     * @return array
     */
    public function get_excluded_dir()
    {
        $items = $this->get_items();
        return $items['directory'] ?? [];
    }
    /**
     * Get a list of excludes
     *
     * @return array
     */
    protected function get_items()
    {
        $this->init_data();
        return $this->data['exclude'] ?? [];
    }
    /**
     * Initialize data array
     *
     * @return void
     */
    protected function init_data()
    {
        if ($this->data === null) {
            $this->data = $this->read();
        }
    }
    /**
     * @inheritdoc
     *
     * @since 100.1.0
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_default_scope;
        $result = [];
        $parents = (array) $this->_file_resolver->get_parents($this->_file_name, $scope);
        // Sort parents desc
        krsort($parents);
        foreach ($parents as $parent) {
            $result = array_replace_recursive($result, $this->_read_files([$parent]));
        }
        return array_replace_recursive($result, parent::read($scope));
    }
}