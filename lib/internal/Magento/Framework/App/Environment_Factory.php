<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Object_Manager\Config_Loader;
use Magento\Framework\App\Object_Manager\Environment\Compiled;
use Magento\Framework\App\Object_Manager\Environment\Developer;
use Magento\Framework\Object_Manager\Definition_Interface;
use Magento\Framework\Object_Manager\Relations_Interface;
class Environment_Factory
{
    /**
     * @var RelationsInterface
     */
    private $relations;
    /**
     * @var DefinitionInterface
     */
    private $definitions;
    /**
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
     */
    public function __construct(Relations_Interface $relations, Definition_Interface $definitions)
    {
        $this->relations = $relations;
        $this->definitions = $definitions;
    }
    /**
     * Create Environment object
     *
     * @return EnvironmentInterface
     */
    public function create_environment()
    {
        switch ($this->get_mode()) {
            case Compiled::MODE:
                return new Compiled($this);
                break;
            default:
                return new Developer($this);
        }
    }
    /**
     * Determinate running mode
     *
     * @return string
     */
    private function get_mode()
    {
        if (file_exists(Config_Loader\Compiled::get_file_path(Area::AREA_GLOBAL))) {
            return Compiled::MODE;
        }
        return Developer::MODE;
    }
    /**
     * Returns definitions
     *
     * @return DefinitionInterface
     */
    public function get_definitions()
    {
        return $this->definitions;
    }
    /**
     * Returns relations
     *
     * @return RelationsInterface
     */
    public function get_relations()
    {
        return $this->relations;
    }
}