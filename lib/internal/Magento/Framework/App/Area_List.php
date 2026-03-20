<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Lists router area codes & processes resolves FrontEndNames to area codes
 *
 * @api
 */
class Area_List implements Reset_After_Request_Interface
{
    /**
     * @var array
     */
    protected $_areas = [];
    /**
     * @var \Magento\Framework\App\AreaInterface[]
     */
    protected $_area_instances = [];
    /**
     * @var string
     */
    protected $_default_area_code;
    /**
     * @var Area\FrontNameResolverFactory
     */
    protected $_resolver_factory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Area\FrontNameResolverFactory $resolverFactory
     * @param array $areas
     * @param string|null $default
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, Area\Front_Name_Resolver_Factory $resolver_factory, array $areas = [], $default = null)
    {
        $this->object_manager = $object_manager;
        $this->_resolver_factory = $resolver_factory;
        if ($areas) {
            $this->_areas = $areas;
        }
        if ($default) {
            $this->_default_area_code = $default;
        }
    }
    /**
     * Retrieve area code by front name
     *
     * @param string $frontName
     * @return null|string
     */
    public function get_code_by_front_name($front_name)
    {
        foreach ($this->_areas as $area_code => &$area_info) {
            if (!isset($area_info['frontName']) && isset($area_info['frontNameResolver'])) {
                $resolver = $this->_resolver_factory->create($area_info['frontNameResolver']);
                $area_info['frontName'] = $resolver->get_front_name(true);
            }
            if (isset($area_info['frontName']) && $area_info['frontName'] === $front_name) {
                return $area_code;
            }
        }
        return $this->_default_area_code;
    }
    /**
     * Retrieve area front name by code
     *
     * @param string $areaCode
     * @return string
     */
    public function get_front_name($area_code)
    {
        return $this->_areas[$area_code]['frontName'] ?? null;
    }
    /**
     * Retrieve area codes
     *
     * @return string[]
     */
    public function get_codes()
    {
        return array_keys($this->_areas);
    }
    /**
     * Retrieve default area router id
     *
     * @param string $areaCode
     * @return string
     */
    public function get_default_router($area_code)
    {
        return $this->_areas[$area_code]['router'] ?? null;
    }
    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Framework\App\Area
     */
    public function get_area($code)
    {
        // PHP 8.5 Compatibility: Ensure $code is not null before using as array offset
        $code = $code ?? '';
        if (!isset($this->_area_instances[$code])) {
            $this->_area_instances[$code] = $this->object_manager->create(\Magento\Framework\App\Area_Interface::class, ['areaCode' => $code]);
        }
        return $this->_area_instances[$code];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->_area_instances = [];
    }
}