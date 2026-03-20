<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components.
 *
 * @api
 * @since 100.0.2
 */
class Component_Registrar implements Component_Registrar_Interface
{
    /**#@+
     * Different types of components
     */
    public const MODULE = 'module';
    public const LIBRARY = 'library';
    public const THEME = 'theme';
    public const LANGUAGE = 'language';
    public const SETUP = 'setup';
    /**#@- */
    /**#@- */
    private static $paths = [self::MODULE => [], self::LIBRARY => [], self::LANGUAGE => [], self::THEME => [], self::SETUP => []];
    /**
     * Sets the location of a component.
     *
     * @param string $type component type
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @throws \LogicException
     * @return void
     */
    public static function register($type, $component_name, $path)
    {
        self::validate_type($type);
        if (isset(self::$paths[$type][$component_name])) {
            throw new \LogicException(ucfirst($type) . ' \'' . $component_name . '\' from \'' . $path . '\' ' . 'has been already defined in \'' . self::$paths[$type][$component_name] . '\'.');
        }
        self::$paths[$type][$component_name] = str_replace('\\', '/', $path);
    }
    /**
     * @inheritdoc
     */
    public function get_paths($type)
    {
        self::validate_type($type);
        return self::$paths[$type];
    }
    /**
     * @inheritdoc
     */
    public function get_path($type, $component_name)
    {
        self::validate_type($type);
        return self::$paths[$type][$component_name] ?? null;
    }
    /**
     * Checks if type of component is valid
     *
     * @param string $type
     * @return void
     * @throws \LogicException
     */
    private static function validate_type($type)
    {
        if (!isset(self::$paths[$type])) {
            throw new \LogicException('\'' . $type . '\' is not a valid component type');
        }
    }
}