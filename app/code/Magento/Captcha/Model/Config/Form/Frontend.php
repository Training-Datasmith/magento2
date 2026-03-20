<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Captcha image model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config\Form;

class Frontend extends \Magento\Captcha\Model\Config\Form\Abstract_Form
{
    /**
     * @var string
     */
    protected $_config_path = 'captcha/frontend/areas';
}