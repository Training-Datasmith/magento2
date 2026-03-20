<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Configuration XML-files merger
 */
namespace Magento\Framework\Config;

/**
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Xml
{
    /**
     * Data extracted from the merged configuration files
     *
     * @var array
     */
    protected $_data;
    /**
     * Dom configuration model
     * @var \Magento\Framework\Config\Dom
     */
    protected $_dom_config = null;
    /**
     * @var \Magento\Framework\Config\DomFactory
     */
    protected $dom_factory;
    /**
     * Instantiate with the list of files to merge
     *
     * @param array $configFiles
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @throws \InvalidArgumentException
     */
    public function __construct($config_files, \Magento\Framework\Config\Dom_Factory $dom_factory)
    {
        $this->dom_factory = $dom_factory;
        if (empty($config_files)) {
            throw new \InvalidArgumentException('There must be at least one configuration file specified.');
        }
        $this->_data = $this->_extract_data($this->_merge($config_files));
    }
    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    abstract public function get_schema_file();
    /**
     * Get absolute path to per-file XML-schema file
     *
     * @return string
     */
    public function get_per_file_schema_file()
    {
        return null;
    }
    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    abstract protected function _extract_data(\Dom_Document $dom);
    /**
     * Merge the config XML-files
     *
     * @param array $configFiles
     * @return \DOMDocument
     * @throws \Magento\Framework\Exception\LocalizedException If a non-existing or invalid XML-file passed
     */
    protected function _merge($config_files)
    {
        foreach ($config_files as $key => $content) {
            try {
                $this->_get_dom_config_model()->merge($content);
            } catch (\Magento\Framework\Config\Dom\Validation_Exception $e) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.", [$key, $e->get_message()]));
            }
        }
        $this->_perform_validate();
        return $this->_get_dom_config_model()->get_dom();
    }
    /**
     * Perform xml validation
     *
     * @param string $file
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException If invalid XML-file passed
     */
    protected function _perform_validate($file = null)
    {
        $errors = [];
        $this->_get_dom_config_model()->validate($this->get_schema_file(), $errors);
        if (!empty($errors)) {
            $phrase = null === $file ? new \Magento\Framework\Phrase('Invalid Document %1%2', [PHP_EOL, implode("\n", $errors)]) : new \Magento\Framework\Phrase('Invalid XML-file: %1%2%3', [$file, PHP_EOL, implode("\n", $errors)]);
            throw new \Magento\Framework\Exception\Localized_Exception($phrase);
        }
        return $this;
    }
    /**
     * Get Dom configuration model
     *
     * @return \Magento\Framework\Config\Dom
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _get_dom_config_model()
    {
        if (null === $this->_dom_config) {
            $this->_dom_config = $this->dom_factory->create_dom(['xml' => $this->_get_initial_xml(), 'idAttributes' => $this->_get_id_attributes(), 'schemaFile' => $this->get_per_file_schema_file()]);
        }
        return $this->_dom_config;
    }
    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    abstract protected function _get_initial_xml();
    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    abstract protected function _get_id_attributes();
}