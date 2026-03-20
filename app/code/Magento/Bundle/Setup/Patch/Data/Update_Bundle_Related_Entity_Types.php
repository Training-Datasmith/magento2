<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Setup\Patch\Data;

use Magento\Catalog\Api\Data\Product_Attribute_Interface;
use Magento\Eav\Setup\Eav_Setup;
use Magento\Eav\Setup\Eav_Setup_Factory;
use Magento\Framework\Setup\Module_Data_Setup_Interface;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
use Magento\Framework\Setup\Patch\Patch_Version_Interface;
/**
 * Class UpdateBundleRelatedEntityTypes
 *
 * @package Magento\Bundle\Setup\Patch
 */
class Update_Bundle_Related_Entity_Types implements Data_Patch_Interface, Patch_Version_Interface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $module_data_setup;
    /**
     * @var EavSetupFactory
     */
    private $eav_setup_factory;
    /**
     * UpdateBundleRelatedEntityTypes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(Module_Data_Setup_Interface $module_data_setup, \Magento\Eav\Setup\Eav_Setup_Factory $eav_setup_factory)
    {
        $this->module_data_setup = $module_data_setup;
        $this->eav_setup_factory = $eav_setup_factory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eav_setup = $this->eav_setup_factory->create(['setup' => $this->module_data_setup]);
        $attribute_set_id = $eav_setup->get_default_attribute_set_id(Product_Attribute_Interface::ENTITY_TYPE_CODE);
        $eav_setup->add_attribute_group(Product_Attribute_Interface::ENTITY_TYPE_CODE, $attribute_set_id, 'Bundle Items', 16);
        $this->upgrade_price_type($eav_setup);
        $this->upgrade_sku_type($eav_setup);
        $this->upgrade_weight_type($eav_setup);
        $this->upgrade_shipment_type($eav_setup);
    }
    /**
     * Upgrade Dynamic Price attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgrade_price_type(Eav_Setup $eav_setup)
    {
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'price_type', 'frontend_input', 'boolean', 31);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'price_type', 'frontend_label', 'Dynamic Price');
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'price_type', 'default_value', 0);
    }
    /**
     * Upgrade Dynamic Sku attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgrade_sku_type(Eav_Setup $eav_setup)
    {
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'sku_type', 'frontend_input', 'boolean', 21);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'sku_type', 'frontend_label', 'Dynamic SKU');
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'sku_type', 'default_value', 0);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'sku_type', 'is_visible', 1);
    }
    /**
     * Upgrade Dynamic Weight attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgrade_weight_type(Eav_Setup $eav_setup)
    {
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'weight_type', 'frontend_input', 'boolean', 71);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'weight_type', 'frontend_label', 'Dynamic Weight');
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'weight_type', 'default_value', 0);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'weight_type', 'is_visible', 1);
    }
    /**
     * Upgrade Ship Bundle Items attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgrade_shipment_type(Eav_Setup $eav_setup)
    {
        $attribute_set_id = $eav_setup->get_default_attribute_set_id(Product_Attribute_Interface::ENTITY_TYPE_CODE);
        $eav_setup->add_attribute_to_group(Product_Attribute_Interface::ENTITY_TYPE_CODE, $attribute_set_id, 'Bundle Items', 'shipment_type', 1);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'shipment_type', 'frontend_input', 'select');
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'shipment_type', 'frontend_label', 'Ship Bundle Items');
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'shipment_type', 'source_model', \Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type::class);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'shipment_type', 'default_value', 0);
        $eav_setup->update_attribute(Product_Attribute_Interface::ENTITY_TYPE_CODE, 'shipment_type', 'is_visible', 1);
    }
    /**
     * @inheritdoc
     */
    public static function get_dependencies()
    {
        return [Apply_Attributes_Update::class];
    }
    /**
     * @inheritdoc
     */
    public static function get_version()
    {
        return '2.0.2';
    }
    /**
     * @inheritdoc
     */
    public function get_aliases()
    {
        return [];
    }
}