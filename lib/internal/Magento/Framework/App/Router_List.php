<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Used as a container for list of routers.
 */
class Router_List implements Router_List_Interface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * List of routers
     *
     * @var RouterInterface[]
     */
    protected $router_list;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $routerList
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, array $router_list)
    {
        $this->object_manager = $object_manager;
        $this->router_list = array_filter($router_list, function ($item) {
            return (!isset($item['disable']) || !$item['disable']) && $item['class'];
        });
        uasort($this->router_list, [$this, 'compareRoutersSortOrder']);
    }
    /**
     * Retrieve router instance by id
     *
     * @param string $routerId
     * @return RouterInterface
     */
    protected function get_router_instance($router_id)
    {
        if (!isset($this->router_list[$router_id]['object'])) {
            $this->router_list[$router_id]['object'] = $this->object_manager->create($this->router_list[$router_id]['class']);
        }
        return $this->router_list[$router_id]['object'];
    }
    /**
     * @inheritDoc
     *
     * @return RouterInterface
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        return $this->get_router_instance($this->key());
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        next($this->router_list);
    }
    /**
     * @inheritDoc
     *
     * @return string|int|null
     */
    #[\Return_Type_Will_Change]
    public function key()
    {
        return key($this->router_list);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function valid()
    {
        return !!current($this->router_list);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        reset($this->router_list);
    }
    /**
     * Compare routers sortOrder
     *
     * @param array $routerDataFirst
     * @param array $routerDataSecond
     * @return int
     */
    protected function compare_routers_sort_order($router_data_first, $router_data_second)
    {
        return (int) $router_data_first['sortOrder'] <=> (int) $router_data_second['sortOrder'];
    }
}