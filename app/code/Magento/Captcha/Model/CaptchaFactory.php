<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Model;

class Captcha_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Get captcha instance
     *
     * @param string $captchaType
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     * @throws \InvalidArgumentException
     */
    public function create($captcha_type, $form_id)
    {
        $class_name = 'Magento\Captcha\Model\\' . ucfirst($captcha_type);
        $instance = $this->_object_manager->create($class_name, ['formId' => $form_id]);
        if (!$instance instanceof \Magento\Captcha\Model\Captcha_Interface) {
            throw new \InvalidArgumentException($class_name . ' does not implement \Magento\Captcha\Model\CaptchaInterface');
        }
        return $instance;
    }
}