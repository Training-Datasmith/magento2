<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Area_List;
/**
 * Scope config
 */
class Scope implements Scope_Interface, Scope_List_Interface
{
    /**
     * Current config scope
     *
     * @var string
     */
    protected $_current_scope;
    /**
     * List of all available areas
     *
     * @var AreaList
     */
    protected $_area_list;
    /**
     * Constructor
     *
     * @param AreaList $areaList
     * @param string $defaultScope
     */
    public function __construct(Area_List $area_list, $default_scope = 'primary')
    {
        $this->_current_scope = $default_scope;
        $this->_area_list = $area_list;
    }
    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function get_current_scope()
    {
        return $this->_current_scope;
    }
    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     */
    public function set_current_scope($scope)
    {
        $this->_current_scope = $scope;
    }
    /**
     * Retrieve list of available config scopes
     *
     * @return string[]
     */
    public function get_all_scopes()
    {
        $codes = $this->_area_list->get_codes();
        array_unshift($codes, 'global');
        array_unshift($codes, 'primary');
        return $codes;
    }
}