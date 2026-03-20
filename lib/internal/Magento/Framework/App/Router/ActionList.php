<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\State;
use Magento\Framework\App\Utility\Reflection_Class_Factory;
use Magento\Framework\Config\Cache_Interface;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer_Interface;
use Reflection_Exception;
/**
 * Class to retrieve action class.
 */
class Action_List
{
    /**
     * Not allowed string in route's action path to avoid disclosing admin url
     */
    public const NOT_ALLOWED_IN_NAMESPACE_PATH = 'adminhtml';
    /**
     * List of application actions
     *
     * @var array
     */
    protected $actions;
    /**
     * @var array
     */
    protected $reserved_words = ['abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'void', 'while', 'xor', 'yield'];
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var string
     */
    private $action_interface;
    /**
     * @var ReflectionClassFactory|null
     */
    private $reflection_class_factory;
    /**
     * @param CacheInterface $cache
     * @param ModuleReader $moduleReader
     * @param string $actionInterface
     * @param string $cacheKey
     * @param array $reservedWords
     * @param SerializerInterface|null $serializer
     * @param State|null $state
     * @param DirectoryList|null $directoryList
     * @param ReflectionClassFactory|null $reflectionClassFactory
     * @throws FileSystemException
     */
    public function __construct(Cache_Interface $cache, Module_Reader $module_reader, $action_interface = Action_Interface::class, $cache_key = 'app_action_list', $reserved_words = [], ?Serializer_Interface $serializer = null, ?State $state = null, ?Directory_List $directory_list = null, ?Reflection_Class_Factory $reflection_class_factory = null)
    {
        $this->reserved_words = array_merge($reserved_words, $this->reserved_words);
        $this->action_interface = $action_interface;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serialize::class);
        $state = $state ?: Object_Manager::get_instance()->get(State::class);
        $this->reflection_class_factory = $reflection_class_factory ?: Object_Manager::get_instance()->get(Reflection_Class_Factory::class);
        if ($state->get_mode() === State::MODE_PRODUCTION) {
            $directory_list = $directory_list ?: Object_Manager::get_instance()->get(Directory_List::class);
            $file = $directory_list->get_path(Directory_List::GENERATED_METADATA) . '/' . $cache_key . '.' . 'php';
            if (file_exists($file)) {
                $this->actions = (include $file) ?? $module_reader->get_action_files();
            } else {
                $this->actions = $module_reader->get_action_files();
            }
        } else {
            $data = $cache->load($cache_key);
            if (!$data) {
                $this->actions = $module_reader->get_action_files();
                $cache->save($this->serializer->serialize($this->actions), $cache_key);
            } else {
                $this->actions = $this->serializer->unserialize($data);
            }
        }
    }
    /**
     * Retrieve action class
     *
     * @param string $module
     * @param string $area
     * @param string $namespace
     * @param string $action
     * @return null|string
     * @throws ReflectionException
     */
    public function get($module, $area, $namespace, $action)
    {
        if ($area) {
            $area = '\\' . $area;
        }
        $namespace = $namespace !== null ? strtolower($namespace) : '';
        if (strpos($namespace, self::NOT_ALLOWED_IN_NAMESPACE_PATH) !== false) {
            return null;
        }
        if ($action && in_array(strtolower($action), $this->reserved_words)) {
            $action .= 'action';
        }
        $full_path = str_replace('_', '\\', strtolower($module . '\controller' . $area . '\\' . $namespace . '\\' . $action));
        try {
            if ($this->validate_action_class($full_path)) {
                return $this->actions[$full_path];
            }
        } catch (Reflection_Exception $e) {
            return null;
        }
        return null;
    }
    /**
     * Validate Action Class
     *
     * @param string $fullPath
     * @return bool
     * @throws ReflectionException
     */
    private function validate_action_class(string $full_path): bool
    {
        if (isset($this->actions[$full_path])) {
            if (!is_subclass_of($this->actions[$full_path], $this->action_interface)) {
                return false;
            }
            $reflection_class = $this->reflection_class_factory->create($this->actions[$full_path]);
            if ($reflection_class->is_instantiable()) {
                return true;
            }
        }
        return false;
    }
}