<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Utility;

use Magento\Framework\Component\Component_Registrar;
/**
 * Utility for class names processing
 */
class Classes
{
    /**
     * virtual class declarations collected from the whole system
     *
     * @var array
     */
    protected static $_virtual_classes = [];
    /**
     * Find all unique matches in specified content using specified PCRE
     *
     * @param string $contents
     * @param string $regex
     * @param array &$result
     * @return array
     */
    public static function get_all_matches($contents, $regex, &$result = [])
    {
        preg_match_all($regex, $contents, $matches);
        array_shift($matches);
        foreach ($matches as $row) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $result = array_merge($result, $row);
        }
        $result = array_filter(array_unique($result), function ($value) {
            return !empty($value);
        });
        return $result;
    }
    /**
     * Get XML node text values using specified xPath
     *
     * The node must contain specified attribute
     *
     * @param \SimpleXMLElement $xml
     * @param string $xPath
     * @return array
     */
    public static function get_xml_node_values(\Simple_Xml_Element $xml, $x_path)
    {
        $result = [];
        $nodes = $xml->xpath($x_path) ?: [];
        foreach ($nodes as $node) {
            $result[] = (string) $node;
        }
        return $result;
    }
    /**
     * Get XML node names using specified xPath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xpath
     * @return array
     */
    public static function get_xml_node_names(\Simple_Xml_Element $xml, $xpath)
    {
        $result = [];
        $nodes = $xml->xpath($xpath) ?: [];
        foreach ($nodes as $node) {
            $result[] = $node->get_name();
        }
        return $result;
    }
    /**
     * Get XML node attribute values using specified xPath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xPath
     * @param string $attributeName
     * @return array
     */
    public static function get_xml_attribute_values(\Simple_Xml_Element $xml, $x_path, $attribute_name)
    {
        $result = [];
        $nodes = $xml->xpath($x_path) ?: [];
        foreach ($nodes as $node) {
            $node = (array) $node;
            if (isset($node['@attributes'][$attribute_name])) {
                $result[] = $node['@attributes'][$attribute_name];
            }
        }
        return $result;
    }
    /**
     * Extract class name from a conventional callback specification "Class::method"
     *
     * @param string $callbackName
     * @return string
     */
    public static function get_callback_class($callback_name)
    {
        $class = explode('::', $callback_name);
        return $class[0];
    }
    /**
     * Find classes in a configuration XML-file (assumes any files under Namespace/Module/etc/*.xml)
     *
     * @param \SimpleXMLElement $xml
     * @return array
     */
    public static function collect_classes_in_config(\Simple_Xml_Element $xml)
    {
        // @todo this method must be refactored after implementation of MAGETWO-7689 (valid configuration)
        $classes = self::get_xml_node_values($xml, '
            /config//resource_adapter | /config/*[not(name()="sections")]//class[not(ancestor::observers)]
                | //model[not(parent::connection)] | //backend_model | //source_model | //price_model
                | //model_token | //writer_model | //clone_model | //frontend_model | //working_model
                | //admin_renderer | //renderer | /config/*/di/preferences/*');
        $classes = array_merge($classes, self::get_xml_attribute_values($xml, '//@backend_model', 'backend_model'));
        $classes = array_merge($classes, self::get_xml_node_names($xml, '/logging/*/expected_models/* | /logging/*/actions/*/expected_models/* | /config/*/di/preferences/*'));
        $classes = array_map([\Magento\Framework\App\Utility\Classes::class, 'getCallbackClass'], $classes);
        $classes = array_map('trim', $classes);
        $classes = array_unique($classes);
        $classes = array_filter($classes, function ($value) {
            return !empty($value);
        });
        return $classes;
    }
    /**
     * Find classes in a layout configuration XML-file
     *
     * @param \SimpleXMLElement $xml
     * @return array
     */
    public static function collect_layout_classes(\Simple_Xml_Element $xml)
    {
        $classes = self::get_xml_attribute_values($xml, '/layout//block[@class]', 'class');
        $classes = array_merge($classes, self::get_xml_node_values($xml, '/layout//action/attributeType | /layout//action[@method="addTab"]/content
                | /layout//action[@method="addMergeSettingsBlockType"
                    or @method="addInformationRenderer"
                    or @method="addDatabaseBlock"]/*[2]
                | /layout//action[@method="setMassactionBlockName"]/name
                | /layout//action[@method="setEntityModelClass"]/code'));
        return array_unique($classes);
    }
    /**
     * Scan application source code and find classes
     *
     * Sub-type pattern allows to distinguish "type" of a class within a module (for example, Block, Model)
     * Returns array(<class> => <module>)
     *
     * @param string $subTypePattern
     * @return array
     */
    public static function collect_module_classes($sub_type_pattern = '[A-Za-z]+')
    {
        $component_registrar = new Component_Registrar();
        $result = [];
        foreach ($component_registrar->get_paths(Component_Registrar::MODULE) as $module_name => $module_path) {
            $pattern = '/^' . preg_quote($module_path, '/') . '\/(' . $sub_type_pattern . '\/.+)\.php$/';
            foreach (Files::init()->get_files([$module_path], '*.php') as $file) {
                if ($file && preg_match($pattern, $file)) {
                    $partial_file_name = substr($file, strlen($module_path ?? '') + 1);
                    $partial_file_name = substr($partial_file_name, 0, strlen($partial_file_name) - strlen('.php'));
                    $partial_class_name = str_replace('/', '\\', $partial_file_name);
                    $class_name = str_replace('_', '\\', $module_name) . '\\' . $partial_class_name;
                    $result[$class_name] = $module_name;
                }
            }
        }
        return $result;
    }
    /**
     * Fetch virtual class declarations from DI configs
     *
     * @return array
     */
    public static function get_virtual_classes()
    {
        if (!empty(self::$_virtual_classes)) {
            return self::$_virtual_classes;
        }
        $config_files = Files::init()->get_di_configs();
        foreach ($config_files as $file_name) {
            $config_dom = new \Dom_Document();
            $config_dom->load($file_name);
            $x_path = new \Domx_Path($config_dom);
            $v_types = $x_path->query('/config/virtualType');
            /** @var \DOMNode $virtualType */
            foreach ($v_types as $virtual_type) {
                $name = $virtual_type->attributes->get_named_item('name')->text_content;
                if (!$virtual_type->attributes->get_named_item('type')) {
                    continue;
                }
                $type = $virtual_type->attributes->get_named_item('type')->text_content;
                self::$_virtual_classes[$name] = $type;
            }
        }
        return self::$_virtual_classes;
    }
    /**
     * Check if instance is virtual type
     *
     * @param string $className
     * @return bool
     */
    public static function is_virtual($class_name)
    {
        //init virtual classes if necessary
        self::get_virtual_classes();
        return array_key_exists($class_name, self::$_virtual_classes);
    }
    /**
     * Get real type name for virtual type
     *
     * @param string $className
     * @return string
     */
    public static function resolve_virtual_type($class_name)
    {
        if (false == self::is_virtual($class_name)) {
            return $class_name;
        }
        $resolved_name = self::$_virtual_classes[$class_name];
        return self::resolve_virtual_type($resolved_name);
    }
    /**
     * Check class is auto-generated
     *
     * @param string $className
     * @return bool
     */
    public static function is_autogenerated($class_name)
    {
        if ($class_name && preg_match('/.*\\\\[a-zA-Z0-9]{1,}(Factory|SearchResults|DataBuilder|Extension|ExtensionInterface)$/', $class_name) || preg_match('/Magento\\\\[\w]+\\\\(Test\\\\(Page|Fixture))\\\\/', $class_name) || preg_match('/.*\\\\[a-zA-Z0-9]{1,}\\\\Proxy$/', $class_name)) {
            return true;
        }
        return false;
    }
    /**
     * Scan contents as PHP-code and find class name occurrences
     *
     * @param string $contents
     * @param array &$classes
     * @return array
     */
    public static function collect_php_code_classes($contents, &$classes = [])
    {
        self::get_all_matches($contents, '/
            # ::getModel ::getSingleton ::getResourceModel ::getResourceSingleton
            \:\:get(?:Resource)?(?:Model | Singleton)\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # addBlock createBlock getBlockSingleton
            | (?:addBlock | createBlock | getBlockSingleton)\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # various methods, first argument
            | \->(?:initReport | setEntityModelClass
                | setAttributeModel | setBackendModel | setFrontendModel | setSourceModel | setModel
            )\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # various methods, second argument
            | \->add(?:ProductConfigurationHelper | OptionsRenderCfg)\(.+,\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # models in install or setup
            | [\'"](?:resource_model | attribute_model | entity_model | entity_attribute_collection
                | source | backend | frontend | input_renderer | frontend_input_renderer
            )[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]

            # misc
            | function\s_getCollectionClass\(\)\s+{\s+return\s+[\'"]([a-z\d_\/]+)[\'"]
            | (?:_parentResourceModelName | _checkoutType | _apiType)\s*=\s*\'([a-z\d_\/]+)\'
            | \'renderer\'\s*=>\s*\'([a-z\d_\/]+)\'
            | protected\s+\$_(?:form|info|backendForm|iframe)BlockType\s*=\s*[\'"]([^\'"]+)[\'"]

            /Uix', $classes);
        // check ->_init | parent::_init
        $skip_for_init = implode('|', ['id', '[\w\d_]+_id', 'pk', 'code', 'status', 'serial_number', 'entity_pk_value', 'currency_code', 'unique_key']);
        self::get_all_matches($contents, '/
            (?:parent\:\: | \->)_init\(\s*[\'"]([^\'"]+)[\'"]\s*\)
            | (?:parent\:\: | \->)_init\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]((?!(' . $skip_for_init . '))[^\'"]+)[\'"]\s*\)
            /Uix', $classes);
        return $classes;
    }
    /**
     * Retrieve module name by class
     *
     * @param string $class
     * @return string
     */
    public static function get_class_module_name($class)
    {
        $parts = explode('\\', trim($class ?: '', '\\'));
        return $parts[0] . '_' . $parts[1];
    }
}