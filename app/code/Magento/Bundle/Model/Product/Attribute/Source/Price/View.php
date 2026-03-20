<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product\Attribute\Source\Price;

use Magento\Eav\Model\Resource_Model\Entity\Attribute\Option_Factory;
use Magento\Framework\DB\Ddl\Table;
/**
 * Bundle Price View Attribute Renderer
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Eav\Model\Entity\Attribute\Source\Abstract_Source
{
    /**
     * @var OptionFactory
     */
    protected $option_factory;
    /**
     * @param OptionFactory $optionFactory
     */
    public function __construct(Option_Factory $option_factory)
    {
        $this->option_factory = $option_factory;
    }
    /**
     * Get all options
     *
     * @return array
     */
    public function get_all_options()
    {
        if (null === $this->_options) {
            $this->_options = [['label' => __('Price Range'), 'value' => 0], ['label' => __('As Low as'), 'value' => 1]];
        }
        return $this->_options;
    }
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function get_option_text($value)
    {
        foreach ($this->get_all_options() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function get_flat_columns()
    {
        $attribute_code = $this->get_attribute()->get_attribute_code();
        return [$attribute_code => ['unsigned' => false, 'default' => null, 'extra' => null, 'type' => Table::TYPE_INTEGER, 'nullable' => true, 'comment' => 'Bundle Price View ' . $attribute_code . ' column']];
    }
    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function get_flat_update_select($store)
    {
        return $this->option_factory->create()->get_flat_update_select($this->get_attribute(), $store, false);
    }
}