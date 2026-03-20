<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Setup\Patch\Data;

use Magento\Eav\Setup\Eav_Setup;
use Magento\Eav\Setup\Eav_Setup_Factory;
use Magento\Framework\Setup\Module_Data_Setup_Interface;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
use Magento\Framework\Setup\Patch\Patch_Version_Interface;
/**
 * Class \Magento\Bundle\Setup\Patch\ApplyAttributesUpdate
 */
class Apply_Attributes_Update implements Data_Patch_Interface, Patch_Version_Interface
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
     * ApplyAttributesUpdate constructor.
     *
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eav_setup = $this->eav_setup_factory->create(['setup' => $this->module_data_setup]);
        $field_list = ['price', 'special_price', 'special_from_date', 'special_to_date', 'minimal_price', 'cost', 'tier_price', 'weight'];
        foreach ($field_list as $field) {
            $apply_to = explode(',', $eav_setup->get_attribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to'));
            if (!in_array(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, $apply_to)) {
                $apply_to[] = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
                $eav_setup->update_attribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to', implode(',', $apply_to));
            }
        }
        $apply_to = explode(',', $eav_setup->get_attribute(\Magento\Catalog\Model\Product::ENTITY, 'cost', 'apply_to'));
        unset($apply_to[array_search(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, $apply_to)]);
        $eav_setup->update_attribute(\Magento\Catalog\Model\Product::ENTITY, 'cost', 'apply_to', implode(',', $apply_to));
        /**
         * Add attributes to the eav/attribute
         */
        $eav_setup->add_attribute(\Magento\Catalog\Model\Product::ENTITY, 'price_type', ['type' => 'int', 'backend' => '', 'frontend' => '', 'label' => '', 'input' => '', 'class' => '', 'source' => '', 'global' => \Magento\Eav\Model\Entity\Attribute\Scoped_Attribute_Interface::SCOPE_GLOBAL, 'visible' => true, 'required' => true, 'user_defined' => false, 'default' => '', 'searchable' => false, 'filterable' => false, 'comparable' => false, 'visible_on_front' => false, 'used_in_product_listing' => true, 'unique' => false, 'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
        $eav_setup->add_attribute(\Magento\Catalog\Model\Product::ENTITY, 'sku_type', ['type' => 'int', 'backend' => '', 'frontend' => '', 'label' => '', 'input' => '', 'class' => '', 'source' => '', 'global' => \Magento\Eav\Model\Entity\Attribute\Scoped_Attribute_Interface::SCOPE_GLOBAL, 'visible' => false, 'required' => true, 'user_defined' => false, 'default' => '', 'searchable' => false, 'filterable' => false, 'comparable' => false, 'visible_on_front' => false, 'unique' => false, 'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
        $eav_setup->add_attribute(\Magento\Catalog\Model\Product::ENTITY, 'weight_type', ['type' => 'int', 'backend' => '', 'frontend' => '', 'label' => '', 'input' => '', 'class' => '', 'source' => '', 'global' => \Magento\Eav\Model\Entity\Attribute\Scoped_Attribute_Interface::SCOPE_GLOBAL, 'visible' => false, 'required' => true, 'user_defined' => false, 'default' => '', 'searchable' => false, 'filterable' => false, 'comparable' => false, 'visible_on_front' => false, 'used_in_product_listing' => true, 'unique' => false, 'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
        $eav_setup->add_attribute(\Magento\Catalog\Model\Product::ENTITY, 'price_view', ['group' => 'Advanced Pricing', 'type' => 'int', 'backend' => '', 'frontend' => '', 'label' => 'Price View', 'input' => 'select', 'class' => '', 'source' => \Magento\Bundle\Model\Product\Attribute\Source\Price\View::class, 'global' => \Magento\Eav\Model\Entity\Attribute\Scoped_Attribute_Interface::SCOPE_GLOBAL, 'visible' => true, 'required' => true, 'user_defined' => false, 'default' => '', 'searchable' => false, 'filterable' => false, 'comparable' => false, 'visible_on_front' => false, 'used_in_product_listing' => true, 'unique' => false, 'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
        $eav_setup->add_attribute(\Magento\Catalog\Model\Product::ENTITY, 'shipment_type', ['type' => 'int', 'backend' => '', 'frontend' => '', 'label' => 'Shipment', 'input' => '', 'class' => '', 'source' => '', 'global' => \Magento\Eav\Model\Entity\Attribute\Scoped_Attribute_Interface::SCOPE_GLOBAL, 'visible' => false, 'required' => true, 'user_defined' => false, 'default' => '', 'searchable' => false, 'filterable' => false, 'comparable' => false, 'visible_on_front' => false, 'used_in_product_listing' => true, 'unique' => false, 'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
    }
    /**
     * @inheritdoc
     */
    public static function get_dependencies()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public static function get_version()
    {
        return '2.0.0';
    }
    /**
     * @inheritdoc
     */
    public function get_aliases()
    {
        return [];
    }
}