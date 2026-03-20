<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Component;

/**
 * Value-object for files found in components
 */
class Component_File
{
    /**
     * Component type
     *
     * @var string
     */
    private $component_type;
    /**
     * Component name
     *
     * @var string
     */
    private $component_name;
    /**
     * Full path
     *
     * @var string
     */
    private $path;
    /**
     * Constructor
     *
     * @param string $componentType
     * @param string $componentName
     * @param string $fullPath
     */
    public function __construct($component_type, $component_name, $full_path)
    {
        $this->component_type = $component_type;
        $this->component_name = $component_name;
        $this->path = $full_path;
    }
    /**
     * Get component type
     *
     * @return string
     */
    public function get_component_type()
    {
        return $this->component_type;
    }
    /**
     * Get component name
     *
     * @return string
     */
    public function get_component_name()
    {
        return $this->component_name;
    }
    /**
     * Get full path to the component
     *
     * @return string
     */
    public function get_full_path()
    {
        return $this->path;
    }
}