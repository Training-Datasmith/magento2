<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller;

use Magento\Framework\Object_Manager_Interface;
/**
 * Result Factory
 *
 * @api
 * @since 100.0.2
 */
class Result_Factory
{
    /**#@+
     * Allowed result types
     */
    public const TYPE_JSON = 'json';
    public const TYPE_RAW = 'raw';
    public const TYPE_REDIRECT = 'redirect';
    public const TYPE_FORWARD = 'forward';
    public const TYPE_LAYOUT = 'layout';
    public const TYPE_PAGE = 'page';
    /**#@-*/
    /**#@-*/
    protected $type_map = [self::TYPE_JSON => Result\Json::class, self::TYPE_RAW => Result\Raw::class, self::TYPE_REDIRECT => Result\Redirect::class, self::TYPE_FORWARD => Result\Forward::class, self::TYPE_LAYOUT => \Magento\Framework\View\Result\Layout::class, self::TYPE_PAGE => \Magento\Framework\View\Result\Page::class];
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $typeMap
     */
    public function __construct(Object_Manager_Interface $object_manager, array $type_map = [])
    {
        $this->object_manager = $object_manager;
        $this->merge_types($type_map);
    }
    /**
     * Add or override result types
     *
     * @param array $typeMap
     * @return void
     */
    protected function merge_types(array $type_map)
    {
        foreach ($type_map as $type_info) {
            if (isset($type_info['type']) && isset($type_info['class'])) {
                $this->type_map[$type_info['type']] = $type_info['class'];
            }
        }
    }
    /**
     * Create new page regarding its type
     *
     * @param string $type
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return ResultInterface
     */
    public function create($type, array $arguments = [])
    {
        if (empty($this->type_map[$type])) {
            throw new \InvalidArgumentException('"' . $type . ': isn\'t allowed');
        }
        $result_instance = $this->object_manager->create($this->type_map[$type], $arguments);
        if (!$result_instance instanceof Result_Interface) {
            throw new \InvalidArgumentException(get_class($result_instance) . ' isn\'t instance of ResultInterface');
        }
        /**
         * TODO: Temporary solution, must be removed after full refactoring to the new result rendering system
         *
         * Used for knowledge how result page was created, page was created through result factory or it's default page
         * in App\View created in constructor
         */
        if ($result_instance instanceof \Magento\Framework\View\Result\Layout) {
            // Initialization has to be in constructor of ResultPage
            $result_instance->add_default_handle();
        }
        return $result_instance;
    }
}