<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\Bundle_Option_Interface;
use Magento\Framework\Model\Abstract_Extensible_Model;
class Bundle_Option extends Abstract_Extensible_Model implements Bundle_Option_Interface
{
    /**#@+
     * Constants
     */
    public const OPTION_ID = 'option_id';
    public const OPTION_QTY = 'option_qty';
    public const OPTION_SELECTIONS = 'option_selections';
    /**#@-*/
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function get_option_id()
    {
        return $this->get_data(self::OPTION_ID);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function get_option_qty()
    {
        return $this->get_data(self::OPTION_QTY);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function get_option_selections()
    {
        return $this->get_data(self::OPTION_SELECTIONS);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function set_option_id($option_id)
    {
        return $this->set_data(self::OPTION_ID, $option_id);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function set_option_qty($option_qty)
    {
        return $this->set_data(self::OPTION_QTY, $option_qty);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function set_option_selections(array $option_selections)
    {
        return $this->set_data(self::OPTION_SELECTIONS, $option_selections);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function get_extension_attributes()
    {
        return $this->_get_extension_attributes();
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Bundle_Option_Extension_Interface $extension_attributes)
    {
        return $this->_set_extension_attributes($extension_attributes);
    }
}