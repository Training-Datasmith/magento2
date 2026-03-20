<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\Patch_Version_Interface;
use Magento\Framework\Setup\Patch\Schema_Patch_Interface;
use Magento\Framework\Setup\Schema_Setup_Interface;
/**
 * Class UpdateBundleRelatedSchema
 *
 * @package Magento\Bundle\Setup\Patch
 */
class Update_Bundle_Related_Schema implements Schema_Patch_Interface, Patch_Version_Interface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schema_setup;
    /**
     * UpdateBundleRelatedSchema constructor.
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(Schema_Setup_Interface $schema_setup)
    {
        $this->schema_setup = $schema_setup;
    }
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schema_setup->start_setup();
        // Updating data of the 'catalog_product_bundle_option_value' table.
        $table_name = $this->schema_setup->get_table('catalog_product_bundle_option_value');
        $select = $this->schema_setup->get_connection()->select()->from(['values' => $table_name], ['value_id'])->join_left(['options' => $this->schema_setup->get_table('catalog_product_bundle_option')], 'values.option_id = options.option_id', ['parent_product_id' => 'parent_id']);
        $this->schema_setup->get_connection()->query($this->schema_setup->get_connection()->insert_from_select($select, $table_name, ['value_id', 'parent_product_id'], \Magento\Framework\DB\Adapter\Adapter_Interface::INSERT_ON_DUPLICATE));
        // Updating data of the 'catalog_product_bundle_selection_price' table.
        $table_name = $this->schema_setup->get_table('catalog_product_bundle_selection_price');
        $tmp_table_name = $this->schema_setup->get_table('catalog_product_bundle_selection_price_tmp');
        $existing_foreign_keys = $this->schema_setup->get_connection()->get_foreign_keys($table_name);
        foreach ($existing_foreign_keys as $key) {
            $this->schema_setup->get_connection()->drop_foreign_key($key['TABLE_NAME'], $key['FK_NAME']);
        }
        $this->schema_setup->get_connection()->create_table($this->schema_setup->get_connection()->create_table_by_ddl($table_name, $tmp_table_name));
        foreach ($existing_foreign_keys as $key) {
            $this->schema_setup->get_connection()->add_foreign_key($key['FK_NAME'], $key['TABLE_NAME'], $key['COLUMN_NAME'], $key['REF_TABLE_NAME'], $key['REF_COLUMN_NAME'], $key['ON_DELETE']);
        }
        $this->schema_setup->get_connection()->query($this->schema_setup->get_connection()->insert_from_select($this->schema_setup->get_connection()->select()->from($table_name), $tmp_table_name));
        $this->schema_setup->get_connection()->truncate_table($table_name);
        $columns_to_select = [];
        foreach ($this->schema_setup->get_connection()->describe_table($tmp_table_name) as $column) {
            $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';
            $columns_to_select[] = $alias . $column['COLUMN_NAME'];
        }
        $select = $this->schema_setup->get_connection()->select()->from(['prices' => $tmp_table_name], [])->join_left(['selections' => $this->schema_setup->get_table('catalog_product_bundle_selection')], 'prices.selection_id = selections.selection_id', [])->columns($columns_to_select);
        $this->schema_setup->get_connection()->query($this->schema_setup->get_connection()->insert_from_select($select, $table_name));
        $this->schema_setup->get_connection()->drop_table($tmp_table_name);
        $this->schema_setup->end_setup();
    }
    /**
     * {@inheritdoc}
     */
    public static function get_dependencies()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public static function get_version()
    {
        return '2.0.4';
    }
    /**
     * {@inheritdoc}
     */
    public function get_aliases()
    {
        return [];
    }
}