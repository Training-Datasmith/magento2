<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Magento configuration XML DOM utility
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Dom\Urn_Resolver;
use Magento\Framework\Config\Dom\Validation_Schema_Exception;
use Magento\Framework\Phrase;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @api
 * @since 100.0.2
 */
class Dom
{
    /**
     * Prefix which will be used for root namespace
     */
    public const ROOT_NAMESPACE_PREFIX = 'x';
    /**
     * Format of items in errors array to be used by default. Available placeholders - fields of \LibXMLError.
     */
    public const ERROR_FORMAT_DEFAULT = "%message%\nLine: %line%\n";
    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    private $validation_state;
    /**
     * Dom document
     *
     * @var \DOMDocument
     */
    protected $dom;
    /**
     * @var Dom\NodeMergingConfig
     */
    protected $node_merging_config;
    /**
     * Name of attribute that specifies type of argument node
     *
     * @var string|null
     */
    protected $type_attribute_name;
    /**
     * Schema validation file
     *
     * @var string
     */
    protected $schema;
    /**
     * Format of error messages
     *
     * @var string
     */
    protected $error_format;
    /**
     * Default namespace for xml elements
     *
     * @var string
     */
    protected $root_namespace;
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    private static $urn_resolver;
    /**
     * @var array
     */
    private static $resolved_schema_paths = [];
    /**
     * Build DOM with initial XML contents and specifying identifier attributes for merging
     *
     * Format of $idAttributes: array('/xpath/to/some/node' => 'id_attribute_name')
     * The path to ID attribute name should not include any attribute notations or modifiers -- only node names
     *
     * @param string $xml
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param array $idAttributes
     * @param string $typeAttributeName
     * @param string $schemaFile
     * @param string $errorFormat
     */
    public function __construct($xml, \Magento\Framework\Config\Validation_State_Interface $validation_state, array $id_attributes = [], $type_attribute_name = null, $schema_file = null, $error_format = self::ERROR_FORMAT_DEFAULT)
    {
        $this->validation_state = $validation_state;
        $this->schema = $schema_file;
        $this->node_merging_config = new Dom\Node_Merging_Config(new Dom\Node_Path_Matcher(), $id_attributes);
        $this->type_attribute_name = $type_attribute_name;
        $this->error_format = $error_format;
        $this->dom = $this->_init_dom($xml);
        $this->root_namespace = $this->dom->lookup_namespace_uri($this->dom->namespace_uri);
    }
    /**
     * Retrieve array of xml errors
     *
     * @param string $errorFormat
     * @param \DOMDocument|null $dom
     * @return string[]
     */
    private static function get_xml_errors($error_format, $dom = null)
    {
        $errors = [];
        $validation_errors = libxml_get_errors();
        if (count($validation_errors)) {
            foreach ($validation_errors as $error) {
                $errors[] = self::_render_error_message($error, $error_format, $dom);
            }
        } else {
            $errors[] = 'Unknown validation error';
        }
        return $errors;
    }
    /**
     * Merge $xml into DOM document
     *
     * @param string $xml
     * @return void
     */
    public function merge($xml)
    {
        $dom = $this->_init_dom($xml);
        $this->_merge_node($dom->document_element, '');
    }
    /**
     * Recursive merging of the \DOMElement into the original document
     *
     * Algorithm:
     * 1. Find the same node in original document
     * 2. Extend and override original document node attributes and scalar value if found
     * 3. Append new node if original document doesn't have the same node
     *
     * @param \DOMElement $node
     * @param string $parentPath path to parent node
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _merge_node(\Dom_Element $node, $parent_path)
    {
        $path = $this->_get_node_path_by_parent($node, $parent_path);
        $matched_node = $this->_get_matched_node($path);
        /* Update matched node attributes and value */
        if ($matched_node) {
            //different node type
            if ($this->type_attribute_name && $node->has_attribute($this->type_attribute_name) && $matched_node->has_attribute($this->type_attribute_name) && $node->get_attribute($this->type_attribute_name) !== $matched_node->get_attribute($this->type_attribute_name)) {
                $parent_matched_node = $this->_get_matched_node($parent_path);
                $new_node = $this->dom->import_node($node, true);
                $parent_matched_node->replace_child($new_node, $matched_node);
                return;
            }
            $this->_merge_attributes($matched_node, $node);
            if (!$node->has_child_nodes()) {
                return;
            }
            /* override node value */
            if ($this->_is_text_node($node)) {
                /* skip the case when the matched node has children, otherwise they get overridden */
                if (!$matched_node->has_child_nodes() || $this->_is_text_node($matched_node) || $this->is_cdata_node($matched_node)) {
                    $matched_node->node_value = $node->child_nodes->item(0)->node_value;
                }
            } elseif ($this->is_cdata_node($node) && $this->_is_text_node($matched_node)) {
                /* Replace text node with CDATA section */
                if ($this->find_cdata_section($node)) {
                    $matched_node->node_value = $this->find_cdata_section($node)->node_value;
                }
            } elseif ($this->is_cdata_node($node) && $this->is_cdata_node($matched_node)) {
                /* Replace CDATA with new one */
                $this->replace_cdata_node($matched_node, $node);
            } else {
                /* recursive merge for all child nodes */
                foreach ($node->child_nodes as $child_node) {
                    if ($child_node instanceof \Dom_Element) {
                        $this->_merge_node($child_node, $path);
                    }
                }
            }
        } else {
            /* Add node as is to the document under the same parent element */
            $parent_matched_node = $this->_get_matched_node($parent_path);
            $new_node = $this->dom->import_node($node, true);
            $parent_matched_node->append_child($new_node);
        }
    }
    /**
     * Check if the node content is text
     *
     * @param \DOMElement $node
     * @return bool
     */
    protected function _is_text_node($node)
    {
        return $node->child_nodes->length == 1 && $node->child_nodes->item(0) instanceof \Dom_Text;
    }
    /**
     * Check if the node content is CDATA (probably surrounded with text nodes) or just text node
     *
     * @param \DOMNode $node
     * @return bool
     */
    private function is_cdata_node($node)
    {
        // If every child node of current is NOT \DOMElement
        // It is arbitrary combination of text nodes and CDATA sections.
        foreach ($node->child_nodes as $child_node) {
            if ($child_node instanceof \Dom_Element) {
                return false;
            }
        }
        return true;
    }
    /**
     * Finds CDATA section from given node children
     *
     * @param \DOMNode $node
     * @return \DOMCdataSection|null
     */
    private function find_cdata_section($node)
    {
        foreach ($node->child_nodes as $child_node) {
            if ($child_node instanceof \Dom_Cdata_Section) {
                return $child_node;
            }
        }
        return null;
    }
    /**
     * Replaces CDATA section in $oldNode with $newNode's
     *
     * @param \DOMNode $oldNode
     * @param \DOMNode $newNode
     */
    private function replace_cdata_node($old_node, $new_node)
    {
        $old_cdata = $this->find_cdata_section($old_node);
        $new_cdata = $this->find_cdata_section($new_node);
        if ($old_cdata && $new_cdata) {
            $old_cdata->node_value = $new_cdata->node_value;
        }
    }
    /**
     * Merges attributes of the merge node to the base node
     *
     * @param \DOMElement $baseNode
     * @param \DOMNode $mergeNode
     * @return void
     */
    protected function _merge_attributes($base_node, $merge_node)
    {
        foreach ($merge_node->attributes as $attribute) {
            $base_node->set_attribute($this->_get_attribute_name($attribute), $attribute->value);
        }
    }
    /**
     * Identify node path based on parent path and node attributes
     *
     * @param \DOMElement $node
     * @param string $parentPath
     * @return string
     */
    protected function _get_node_path_by_parent(\Dom_Element $node, $parent_path)
    {
        $prefix = $this->root_namespace === null ? '' : self::ROOT_NAMESPACE_PREFIX . ':';
        $path = $parent_path . '/' . $prefix . $node->tag_name;
        $id_attribute = $this->node_merging_config->get_id_attribute($path);
        if (is_array($id_attribute)) {
            $constraints = [];
            foreach ($id_attribute as $attribute) {
                $value = $node->get_attribute($attribute);
                $constraints[] = "@{$attribute}='{$value}'";
            }
            $path .= '[' . implode(' and ', $constraints) . ']';
        } elseif ($id_attribute && $value = $node->get_attribute($id_attribute)) {
            $path .= "[@{$id_attribute}='{$value}']";
        }
        return $path;
    }
    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @throws \Magento\Framework\Exception\LocalizedException An exception is possible if original document contains
     *     multiple nodes for identifier
     * @return \DOMElement|null
     */
    protected function _get_matched_node($node_path)
    {
        $x_path = new \Domx_Path($this->dom);
        if ($this->root_namespace) {
            $x_path->register_namespace(self::ROOT_NAMESPACE_PREFIX, $this->root_namespace);
        }
        $matched_nodes = $x_path->query($node_path);
        $node = null;
        if ($matched_nodes->length > 1) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('More than one node matching the query: %1, Xml is: %2', [$node_path, $this->dom->save_xml()]));
        } elseif ($matched_nodes->length == 1) {
            $node = $matched_nodes->item(0);
        }
        return $node;
    }
    /**
     * Validate dom document
     *
     * @param \DOMDocument $dom
     * @param string $schema Absolute schema file path or URN
     * @param string $errorFormat
     * @return array of errors
     * @throws \Exception
     */
    public static function validate_dom_document(\Dom_Document $dom, $schema, $error_format = self::ERROR_FORMAT_DEFAULT)
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            return [];
        }
        if (!self::$urn_resolver) {
            self::$urn_resolver = new Urn_Resolver();
        }
        if (!isset(self::$resolved_schema_paths[$schema])) {
            self::$resolved_schema_paths[$schema] = self::$urn_resolver->get_real_path($schema);
        }
        $schema = self::$resolved_schema_paths[$schema];
        libxml_use_internal_errors(true);
        libxml_set_external_entity_loader([self::$urn_resolver, 'registerEntityLoader']);
        $errors = [];
        try {
            $result = $dom->schema_validate($schema);
            if (!$result) {
                $errors = self::get_xml_errors($error_format, $dom);
            }
        } catch (\Exception $exception) {
            $errors = self::get_xml_errors($error_format);
            libxml_use_internal_errors(false);
            array_unshift($errors, new Phrase('Processed schema file: %1', [$schema]));
            throw new Validation_Schema_Exception(new Phrase(implode("\n", $errors)));
        }
        libxml_set_external_entity_loader(null);
        libxml_use_internal_errors(false);
        return $errors;
    }
    /**
     * Render error message string by replacing placeholders '%field%' with properties of \LibXMLError
     *
     * @param \LibXMLError $errorInfo
     * @param string $format
     * @param \DOMDocument|null $dom
     * @return string
     * @throws \InvalidArgumentException
     */
    private static function _render_error_message(\Lib_Xml_Error $error_info, string $format, ?\Dom_Document $dom = null): string
    {
        $result = $format;
        foreach ($error_info as $field => $value) {
            $placeholder = '%' . $field . '%';
            $value = trim((string) $value);
            $result = $result !== null ? str_replace($placeholder, $value, $result) : '';
        }
        if ($result && strpos($result, '%') !== false) {
            if (preg_match_all('/%.+%/', $result, $matches)) {
                $unsupported = [];
                foreach ($matches[0] as $placeholder) {
                    if (strpos($result, $placeholder) !== false) {
                        $unsupported[] = $placeholder;
                    }
                }
                if (!empty($unsupported)) {
                    throw new \InvalidArgumentException("Error format '{$format}' contains unsupported placeholders: " . implode(', ', $unsupported));
                }
            }
        }
        if ($dom) {
            $xml = explode(PHP_EOL, $dom->save_xml());
            $lines = array_slice($xml, max(0, $error_info->line - 5), 10, true);
            $result .= 'The xml was: ' . PHP_EOL;
            foreach ($lines as $line_number => $line) {
                $result .= $line_number . ':' . $line . PHP_EOL;
            }
        }
        return $result;
    }
    /**
     * DOM document getter
     *
     * @return \DOMDocument
     */
    public function get_dom()
    {
        return $this->dom;
    }
    /**
     * Create DOM document based on $xml parameter
     *
     * @param string $xml
     * @return \DOMDocument
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _init_dom($xml)
    {
        $dom = new \Dom_Document();
        $use_errors = libxml_use_internal_errors(true);
        $res = $dom->load_xml($xml);
        if (!$res) {
            $errors = self::get_xml_errors($this->error_format);
            libxml_use_internal_errors($use_errors);
            throw new \Magento\Framework\Config\Dom\Validation_Exception(implode("\n", $errors));
        }
        libxml_use_internal_errors($use_errors);
        if ($this->validation_state->is_validation_required() && $this->schema) {
            $errors = $this->validate_dom_document($dom, $this->schema, $this->error_format);
            if (count($errors)) {
                throw new \Magento\Framework\Config\Dom\Validation_Exception(implode("\n", $errors));
            }
        }
        return $dom;
    }
    /**
     * Validate self contents towards to specified schema
     *
     * @param string $schemaFileName absolute path to schema file
     * @param array &$errors
     * @return bool
     */
    public function validate($schema_file_name, &$errors = [])
    {
        if ($this->validation_state->is_validation_required()) {
            $errors = $this->validate_dom_document($this->dom, $schema_file_name, $this->error_format);
            return !count($errors);
        }
        return true;
    }
    /**
     * Set schema file
     *
     * @param string $schemaFile
     * @return $this
     */
    public function set_schema_file($schema_file)
    {
        $this->schema = $schema_file;
        return $this;
    }
    /**
     * Returns the attribute name with prefix, if there is one
     *
     * @param \DOMAttr $attribute
     * @return string
     */
    private function _get_attribute_name($attribute)
    {
        if ($attribute->prefix !== null && !empty($attribute->prefix)) {
            $attribute_name = $attribute->prefix . ':' . $attribute->name;
        } else {
            $attribute_name = $attribute->name;
        }
        return $attribute_name;
    }
}