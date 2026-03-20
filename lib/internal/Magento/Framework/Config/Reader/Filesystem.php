<?php

declare (strict_types=1);
/**
 *  Copyright 2014 Adobe
 *  All Rights Reserved.
 */
namespace Magento\Framework\Config\Reader;

/**
 * Filesystem configuration loader. Loads configuration from XML files, split by scopes
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @since 100.0.2
 */
class Filesystem implements \Magento\Framework\Config\Reader_Interface
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
     * @var \Magento\Framework\Config\ConverterInterface
     */
    protected $_converter;
    /**
     * The name of file that stores configuration
     *
     * @var string
     */
    protected $_file_name;
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;
    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_per_file_schema;
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_id_attributes = [];
    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $_dom_document_class;
    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    protected $validation_state;
    /**
     * @var string
     * @since 100.0.3
     */
    protected $_default_scope;
    /**
     * @var string
     * @since 100.0.3
     */
    protected $_schema_file;
    /**
     * Name of an attribute that stands for data type of node values
     *
     * @var string|null
     */
    private $type_attribute_name;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @param string|null $typeAttributeName
     */
    public function __construct(\Magento\Framework\Config\File_Resolver_Interface $file_resolver, \Magento\Framework\Config\Converter_Interface $converter, \Magento\Framework\Config\Schema_Locator_Interface $schema_locator, \Magento\Framework\Config\Validation_State_Interface $validation_state, $file_name, $id_attributes = [], $dom_document_class = \Magento\Framework\Config\Dom::class, $default_scope = 'global', ?string $type_attribute_name = null)
    {
        $this->_file_resolver = $file_resolver;
        $this->_converter = $converter;
        $this->_file_name = $file_name;
        $this->_id_attributes = array_replace($this->_id_attributes, $id_attributes);
        $this->validation_state = $validation_state;
        $this->_schema_file = $schema_locator->get_schema();
        $this->_per_file_schema = $schema_locator->get_per_file_schema() && $validation_state->is_validation_required() ? $schema_locator->get_per_file_schema() : null;
        $this->_dom_document_class = $dom_document_class;
        $this->_default_scope = $default_scope;
        $this->type_attribute_name = $type_attribute_name;
    }
    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_default_scope;
        $file_list = $this->_file_resolver->get($this->_file_name, $scope);
        if (!count($file_list)) {
            return [];
        }
        $output = $this->_read_files($file_list);
        return $output;
    }
    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _read_files($file_list)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $config_merger = null;
        foreach ($file_list as $key => $content) {
            try {
                if (!$config_merger) {
                    $config_merger = $this->_create_config_merger($this->_dom_document_class, $content);
                } else {
                    $config_merger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\Validation_Exception $e) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.", [$key, $e->get_message()]));
            }
        }
        if ($this->validation_state->is_validation_required()) {
            $errors = [];
            if ($config_merger && !$config_merger->validate($this->_schema_file, $errors)) {
                // The merged XML is invalid, but each XML document is individually valid.
                // (If they had errors, we would have thrown an exception in the loop above.)
                // Let's work out which document is causing us a problem.
                $config_merger = null;
                foreach ($file_list as $key => $content) {
                    if (!$config_merger) {
                        $config_merger = $this->_create_config_merger($this->_dom_document_class, $content);
                    } else {
                        $config_merger->merge($content);
                    }
                    if (!$config_merger->validate($this->_schema_file)) {
                        array_unshift($errors, "Error in merged XML after reading {$key}");
                        break;
                    }
                }
                $message = "Invalid Document \n";
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase($message . implode("\n", $errors)));
            }
        }
        $output = [];
        if ($config_merger) {
            $output = $this->_converter->convert($config_merger->get_dom());
        }
        return $output;
    }
    /**
     * Return newly created instance of a config merger
     *
     * @param string $mergerClass
     * @param string $initialContents
     * @return \Magento\Framework\Config\Dom
     * @throws \UnexpectedValueException
     */
    protected function _create_config_merger($merger_class, $initial_contents)
    {
        $result = new $merger_class($initial_contents, $this->validation_state, $this->_id_attributes, $this->type_attribute_name, $this->_per_file_schema);
        if (!$result instanceof \Magento\Framework\Config\Dom) {
            throw new \UnexpectedValueException("Instance of the DOM config merger is expected, got {$merger_class} instead.");
        }
        return $result;
    }
}