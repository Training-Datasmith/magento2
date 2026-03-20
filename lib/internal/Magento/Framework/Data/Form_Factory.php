<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data;

/**
 * Form factory class
 *
 * @api
 */
class Form_Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instance_name;
    /**
     * Factory construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Data\Form::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create form instance
     *
     * @param array $data
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $data = [])
    {
        /** @var $form \Magento\Framework\Data\Form */
        $form = $this->_object_manager->create($this->_instance_name, $data);
        if (!$form instanceof \Magento\Framework\Data\Form) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('%1 doesn\'t extend \Magento\Framework\Data\Form', [$this->_instance_name]));
        }
        return $form;
    }
}