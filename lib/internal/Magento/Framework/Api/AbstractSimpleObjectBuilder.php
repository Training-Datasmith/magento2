<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Base Builder Class for simple data Objects
 * @deprecated 103.0.0 Every builder should have own implementation of \Magento\Framework\Api\SimpleBuilderInterface
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Abstract_Simple_Object_Builder implements Simple_Builder_Interface, Reset_After_Request_Interface
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var ObjectFactory
     */
    protected $object_factory;
    /**
     * @param ObjectFactory $objectFactory
     */
    public function __construct(Object_Factory $object_factory)
    {
        $this->data = [];
        $this->object_factory = $object_factory;
    }
    /**
     * Builds the Data Object
     *
     * @return AbstractSimpleObject
     */
    public function create()
    {
        $data_object_type = $this->_get_data_object_type();
        $data_object = $this->object_factory->create($data_object_type, ['data' => $this->data]);
        $this->data = [];
        return $data_object;
    }
    /**
     * Overwrite data in Object.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }
    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _get_data_object_type()
    {
        $data_object_type = '';
        $pattern = '/(?<data_object>.*?)Builder(\\\\Interceptor)?/';
        if (preg_match($pattern, get_class($this), $match)) {
            $data_object_type = $match['data_object'];
        }
        return $data_object_type;
    }
    /**
     * Return data Object data.
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->data = [];
    }
}