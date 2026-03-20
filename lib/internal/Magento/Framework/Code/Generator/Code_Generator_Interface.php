<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Code\Generator;

/**
 * Interface \Magento\Framework\Code\Generator\CodeGeneratorInterface
 *
 * @api
 */
interface Code_Generator_Interface extends \Laminas\Code\Generator\Generator_Interface
{
    /**
     * Set class name.
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Set class doc block.
     *
     * @param array $docBlock
     * @return $this
     */
    public function set_class_doc_block(array $doc_block);
    /**
     * Add a list of properties.
     *
     * @param array $properties
     * @return $this
     */
    public function add_properties(array $properties);
    /**
     * Add a list of methods.
     *
     * @param array $methods
     * @return $this
     */
    public function add_methods(array $methods);
    /**
     * Set extended class.
     *
     * @param string $extendedClass
     * @return $this
     */
    public function set_extended_class($extended_class);
    /**
     * Set a list of implemented interfaces.
     *
     * @param array $interfaces
     * @return $this
     */
    public function set_implemented_interfaces(array $interfaces);
    /**
     * Add a trait to the class.
     *
     * @param string $trait
     * @return $this
     */
    public function add_trait($trait);
}