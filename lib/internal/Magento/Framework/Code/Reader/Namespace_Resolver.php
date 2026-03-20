<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

/**
 * Class resolve short namespaces to fully qualified namespaces.
 */
class Namespace_Resolver
{
    /**
     * Namespace separator
     */
    public const NS_SEPARATOR = '\\';
    /**
     * @var ScalarTypesProvider
     */
    private $scalar_types_provider;
    /**
     * @var array
     */
    private $namespaces = [];
    /**
     * NamespaceResolver constructor.
     * @param ScalarTypesProvider $scalarTypesProvider
     */
    public function __construct(?Scalar_Types_Provider $scalar_types_provider = null)
    {
        $this->scalar_types_provider = $scalar_types_provider ?: new Scalar_Types_Provider();
    }
    /**
     * Perform namespace resolution if required and return fully qualified name.
     *
     * @param string $type
     * @param array $availableNamespaces
     * @return string
     */
    public function resolve_namespace($type, array $available_namespaces)
    {
        if (!empty($type) && substr($type, 0, 1) !== self::NS_SEPARATOR && !in_array($type, $this->scalar_types_provider->get_types())) {
            $name = explode(self::NS_SEPARATOR, $type);
            $unqualified_name = $name[0];
            $is_qualified_name = count($name) > 1;
            if (isset($available_namespaces[$unqualified_name])) {
                $namespace = $available_namespaces[$unqualified_name];
                if ($is_qualified_name) {
                    array_shift($name);
                    return $namespace . self::NS_SEPARATOR . implode(self::NS_SEPARATOR, $name);
                }
                return $namespace;
            } else {
                return self::NS_SEPARATOR . $available_namespaces[0] . self::NS_SEPARATOR . $type;
            }
        }
        return $type;
    }
    /**
     * Get all imported namespaces from provided class.
     *
     * @param array $fileContent
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_imported_namespaces(array $file_content)
    {
        $file_content = implode('', $file_content);
        $cache_key = sha1($file_content);
        if (isset($this->namespaces[$cache_key])) {
            return $this->namespaces[$cache_key];
        }
        $file_content = token_get_all($file_content);
        $class_start = array_search('{', $file_content);
        $file_content = array_slice($file_content, 0, $class_start);
        $output = [];
        foreach ($file_content as $position => $token) {
            if (is_array($token) && $token[0] === T_USE) {
                $import = array_slice($file_content, $position);
                $import_end = array_search(';', $import);
                $import = array_slice($import, 0, $import_end);
                $imports = [];
                $imports_count = 0;
                foreach ($import as $item) {
                    if ($item === ',') {
                        $imports_count++;
                        continue;
                    }
                    $imports[$imports_count][] = $item;
                }
                foreach ($imports as $import) {
                    $import = array_filter($import, function ($token) {
                        $whitelist = [T_NS_SEPARATOR => T_NS_SEPARATOR, T_STRING => T_STRING, T_AS => T_AS, T_NAME_QUALIFIED => T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED => T_NAME_FULLY_QUALIFIED];
                        if (isset($token[0], $whitelist[$token[0]])) {
                            return true;
                        }
                        return false;
                    });
                    $import = array_map(function ($element) {
                        return $element[1];
                    }, $import);
                    $import = array_values($import);
                    if ($import[0] === self::NS_SEPARATOR) {
                        array_shift($import);
                    }
                    $import_name = null;
                    if (in_array('as', $import)) {
                        $import_name = array_splice($import, -1)[0];
                        array_pop($import);
                    }
                    $use_statement = implode('', $import);
                    if ($import_name) {
                        $output[$import_name] = self::NS_SEPARATOR . $use_statement;
                    } else {
                        $key = explode(self::NS_SEPARATOR, $use_statement);
                        $key = end($key);
                        $output[$key] = self::NS_SEPARATOR . $use_statement;
                    }
                }
            }
        }
        $this->namespaces[$cache_key] = $output;
        return $this->namespaces[$cache_key];
    }
}