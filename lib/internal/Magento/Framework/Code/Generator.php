<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Code\Generator\Defined_Classes;
use Magento\Framework\Code\Generator\Entity_Abstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Object_Manager\Config_Interface;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Phrase;
use Psr\Log\Logger_Interface;
/**
 * Class code generator.
 */
class Generator
{
    public const GENERATION_SUCCESS = 'success';
    public const GENERATION_ERROR = 'error';
    public const GENERATION_SKIP = 'skip';
    /**
     * @var Io
     */
    protected $_io_object;
    /**
     * @var array
     */
    protected $_generated_entities;
    /**
     * @var DefinedClasses
     */
    protected $defined_classes;
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param Generator\Io $ioObject
     * @param array $generatedEntities
     * @param DefinedClasses $definedClasses
     * @param LoggerInterface|null $logger
     */
    public function __construct(?Io $io_object = null, array $generated_entities = [], ?Defined_Classes $defined_classes = null, ?Logger_Interface $logger = null)
    {
        $this->_io_object = $io_object ?: new Io(new File());
        $this->defined_classes = $defined_classes ?: new Defined_Classes();
        $this->_generated_entities = $generated_entities;
        $this->logger = $logger;
    }
    /**
     * Get generated entities
     *
     * @return array
     */
    public function get_generated_entities()
    {
        return $this->_generated_entities;
    }
    /**
     * Set entity-to-generator map
     *
     * @param array $generatedEntities
     * @return $this
     */
    public function set_generated_entities($generated_entities)
    {
        $this->_generated_entities = $generated_entities;
        return $this;
    }
    /**
     * Generate Class
     *
     * @param string $className
     * @return string | void
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generate_class($class_name)
    {
        $result_entity_type = null;
        $source_class_name = null;
        foreach ($this->_generated_entities as $entity_type => $generator_class) {
            $suffix_len = strlen($entity_type);
            $entity_suffix = ucfirst($entity_type);
            // If $className string ends with $entitySuffix substring
            if (substr_compare($class_name, $entity_suffix, -$suffix_len, $suffix_len) == 0) {
                $result_entity_type = $entity_type;
                $source_class_name = rtrim(substr($class_name, 0, -$suffix_len), '\\');
                break;
            }
        }
        if ($skip_reason = $this->should_skip_generation($result_entity_type, $source_class_name, $class_name)) {
            return $skip_reason;
        }
        $generator_class = $this->_generated_entities[$result_entity_type];
        /** @var EntityAbstract $generator */
        $generator = $this->create_generator_instance($generator_class, $source_class_name, $class_name);
        if ($generator !== null) {
            $this->try_to_load_source_class($class_name, $generator);
            if (!$file = $generator->generate()) {
                /** @var $logger LoggerInterface */
                $errors = $generator->get_errors();
                $errors[] = 'Class ' . $class_name . ' generation error: The requested class did not generate properly, ' . 'because the \'generated\' directory permission is read-only. ' . 'If --- after running the \'bin/magento setup:di:compile\' CLI command when the \'generated\' ' . 'directory permission is set to write --- the requested class did not generate properly, then ' . 'you must add the generated class object to the signature of the related construct method, only.';
                $message = implode(PHP_EOL, $errors);
                $this->get_logger()->critical($message);
                throw new \RuntimeException($message);
            }
            if (!$this->defined_classes->is_class_loadable_from_memory($class_name)) {
                $this->_io_object->include_file($file);
            }
            return self::GENERATION_SUCCESS;
        }
    }
    /**
     * Retrieve logger
     *
     * @return LoggerInterface
     */
    private function get_logger()
    {
        if (!$this->logger) {
            $this->logger = $this->get_object_manager()->get(Logger_Interface::class);
        }
        return $this->logger;
    }
    /**
     * Create entity generator
     *
     * @param string $generatorClass
     * @param string $entityName
     * @param string $className
     * @return EntityAbstract
     */
    protected function create_generator_instance($generator_class, $entity_name, $class_name)
    {
        return $this->get_object_manager()->create($generator_class, ['sourceClassName' => $entity_name, 'resultClassName' => $class_name, 'ioObject' => $this->_io_object]);
    }
    /**
     * Set object manager instance.
     *
     * @param ObjectManagerInterface $objectManager
     * @return $this
     */
    public function set_object_manager(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
        return $this;
    }
    /**
     * Get object manager instance.
     *
     * @return ObjectManagerInterface
     */
    public function get_object_manager()
    {
        if (!$this->object_manager instanceof Object_Manager_Interface) {
            throw new \LogicException('Object manager was expected to be set using setObjectManger() ' . 'before getObjectManager() invocation.');
        }
        return $this->object_manager;
    }
    /**
     * Try to load/generate source class to check if it is valid or not.
     *
     * @param string $className
     * @param EntityAbstract $generator
     * @return void
     * @throws \RuntimeException
     */
    protected function try_to_load_source_class($class_name, $generator)
    {
        $source_class_name = $generator->get_source_class_name();
        if (!$this->defined_classes->is_class_loadable($source_class_name)) {
            if ($this->generate_class($source_class_name) !== self::GENERATION_SUCCESS) {
                $phrase = new Phrase('Source class "%1" for "%2" generation does not exist.', [$source_class_name, $class_name]);
                throw new \RuntimeException($phrase->__toString());
            }
        }
    }
    /**
     * Perform validation surrounding source and result classes and entity type
     *
     * @param string $resultEntityType
     * @param string $sourceClassName
     * @param string $resultClass
     * @return string|bool
     */
    protected function should_skip_generation($result_entity_type, $source_class_name, $result_class)
    {
        if (!$result_entity_type || !$source_class_name) {
            return self::GENERATION_ERROR;
        }
        /** @var ConfigInterface $omConfig */
        $om_config = $this->object_manager->get(Config_Interface::class);
        $virtual_types = $om_config->get_virtual_types();
        /**
         * Do not try to autogenerate virtual types
         * For example virtual types with names overlapping autogenerated suffixes
         */
        if (isset($virtual_types[$result_class])) {
            return self::GENERATION_SKIP;
        }
        if ($this->defined_classes->is_class_loadable_from_disk($result_class)) {
            $generated_file_name = $this->_io_object->generate_result_file_name($result_class);
            /**
             * Must handle two edge cases: a competing process has generated the class and written it to disc already,
             * or the class exists in committed code, despite matching pattern to be generated.
             */
            if ($this->_io_object->file_exists($generated_file_name) && !$this->defined_classes->is_class_loadable_from_memory($result_class)) {
                $this->_io_object->include_file($generated_file_name);
            }
            return self::GENERATION_SKIP;
        }
        if (!isset($this->_generated_entities[$result_entity_type])) {
            throw new \InvalidArgumentException('Unknown generation entity.');
        }
        return false;
    }
}