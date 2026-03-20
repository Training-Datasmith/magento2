<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Data source to fill "Forms" field
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config\Form;

use Magento\Framework\App\Config\Value;
abstract class Abstract_Form extends Value implements \Magento\Framework\Option\Array_Interface
{
    /**
     * @var string
     */
    protected $_config_path;
    /**
     * Returns options for form multiselect
     *
     * @return array
     */
    public function to_option_array()
    {
        $option_array = [];
        $backend_config = $this->_config->get_value($this->_config_path, 'default');
        if ($backend_config) {
            foreach ($backend_config as $form_name => $form_config) {
                if (!empty($form_config['label'])) {
                    $option_array[] = ['label' => $form_config['label'], 'value' => $form_name];
                }
            }
        }
        return $option_array;
    }
}