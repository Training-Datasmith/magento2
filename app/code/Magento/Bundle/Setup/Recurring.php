<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Setup;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Setup\External_Fk_Setup;
use Magento\Framework\Setup\Install_Schema_Interface;
use Magento\Framework\Setup\Module_Context_Interface;
use Magento\Framework\Setup\Schema_Setup_Interface;
/**
 * @codeCoverageIgnore
 */
class Recurring implements Install_Schema_Interface
{
    /**
     * @var MetadataPool
     */
    protected $metadata_pool;
    /**
     * @var ExternalFKSetup
     */
    protected $external_fk_setup;
    /**
     * @param MetadataPool $metadataPool
     * @param ExternalFKSetup $externalFKSetup
     */
    public function __construct(Metadata_Pool $metadata_pool, External_Fk_Setup $external_fk_setup)
    {
        $this->metadata_pool = $metadata_pool;
        $this->external_fk_setup = $external_fk_setup;
    }
    /**
     * {@inheritdoc}
     */
    public function install(Schema_Setup_Interface $setup, Module_Context_Interface $context)
    {
        $installer = $setup;
        $installer->start_setup();
        $list_tables = ['catalog_product_bundle_price_index' => 'entity_id', 'catalog_product_bundle_selection' => 'product_id'];
        foreach ($list_tables as $table_name => $column_name) {
            $this->add_external_foreign_keys($installer, $table_name, $column_name);
        }
        $installer->end_setup();
    }
    /**
     * Add external foreign keys
     *
     * @param SchemaSetupInterface $installer
     * @param string $tableName
     * @param string $columnName
     * @return void
     * @throws \Exception
     */
    protected function add_external_foreign_keys(Schema_Setup_Interface $installer, $table_name, $column_name)
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $this->external_fk_setup->install($installer, $metadata->get_entity_table(), $metadata->get_identifier_field(), $table_name, $column_name);
    }
}