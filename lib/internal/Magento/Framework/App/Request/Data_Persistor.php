<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Request;

use Magento\Framework\Api\Simple_Data_Object_Converter;
use Magento\Framework\Session\Session_Manager_Interface;
/**
 * Persist data to session.
 */
class Data_Persistor implements Data_Persistor_Interface
{
    /**
     * @var SessionManagerInterface
     */
    protected $session;
    /**
     * @param SessionManagerInterface $session
     */
    public function __construct(Session_Manager_Interface $session)
    {
        $this->session = $session;
    }
    /**
     * Store data by key
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function set($key, $data)
    {
        $method = 'set' . Simple_Data_Object_Converter::snake_case_to_upper_camel_case($key) . 'Data';
        call_user_func_array([$this->session, $method], [$data]);
    }
    /**
     * Retrieve data by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $method = 'get' . Simple_Data_Object_Converter::snake_case_to_upper_camel_case($key) . 'Data';
        return call_user_func_array([$this->session, $method], []);
    }
    /**
     * Clear data by key
     *
     * @param string $key
     * @return void
     */
    public function clear($key)
    {
        $method = 'uns' . Simple_Data_Object_Converter::snake_case_to_upper_camel_case($key) . 'Data';
        call_user_func_array([$this->session, $method], []);
    }
}