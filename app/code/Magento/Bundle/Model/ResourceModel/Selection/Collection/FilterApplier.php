<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Selection\Collection;

use Magento\Bundle\Model\Resource_Model\Selection\Collection;
use Zend_Db_Select_Exception;
/**
 * An applier of additional filters to a selection collection.
 *
 * The class is introduced to extend filtering abilities of the collection
 * without backward incompatible changes in a corresponding collection class.
 */
class Filter_Applier
{
    /**
     * @var array
     */
    private $condition_types_map = ['eq' => ' = ?', 'in' => ' IN (?)'];
    /**
     * Applies filter to the given collection in accordance with the given condition.
     *
     * @param Collection $collection
     * @param string $field
     * @param string|array $value
     * @param string $conditionType
     *
     * @return void
     * @throws Zend_Db_Select_Exception
     */
    public function apply(Collection $collection, string $field, $value, string $condition_type = 'eq')
    {
        foreach ($collection->get_select()->get_part('from') as $table_alias => $data) {
            if ($data['tableName'] == $collection->get_table('catalog_product_bundle_selection')) {
                $field = $table_alias . '.' . $field;
            }
        }
        $collection->get_select()->distinct(true)->where($field . $this->condition_types_map[$condition_type], $value);
    }
}