<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/**
 * Resolve URN path to a real schema path
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Component\Component_Registrar;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Phrase;
/**
 * @api
 * @since 100.0.2
 */
class Urn_Resolver
{
    /**
     * Get real file path by it's URN reference
     *
     * @param string $schema
     * @return string
     * @throws NotFoundException
     */
    public function get_real_path($schema)
    {
        if ($schema && strpos($schema, 'urn:') !== 0) {
            return $schema;
        }
        $component_registrar = new Component_Registrar();
        $matches = [];
        $module_pattern = '/urn:(?<vendor>([a-zA-Z]*)):module:(?<module>([A-Za-z0-9\_]*)):(?<path>(.+))/';
        $framework_pattern = '/urn:(?<vendor>([a-zA-Z]*)):(?<framework>(framework[A-Za-z\-]*)):(?<path>(.+))/';
        $setup_pattern = '/urn:(?<vendor>([a-zA-Z]*)):(?<setup>(setup[A-Za-z\-]*)):(?<path>(.+))/';
        if (preg_match($module_pattern, $schema, $matches)) {
            //urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd
            $package = $component_registrar->get_path(Component_Registrar::MODULE, $matches['module']);
        } elseif (preg_match($framework_pattern, $schema, $matches)) {
            //urn:magento:framework:Module/etc/module.xsd
            //urn:magento:framework-amqp:Module/etc/module.xsd
            $package = $component_registrar->get_path(Component_Registrar::LIBRARY, $matches['vendor'] . '/' . $matches['framework']);
        } elseif (preg_match($setup_pattern, $schema, $matches)) {
            //urn:magento:setup:
            $package = $component_registrar->get_path(Component_Registrar::SETUP, $matches['vendor'] . '/' . $matches['setup']);
        } else {
            throw new Not_Found_Exception(new Phrase("Unsupported format of schema location: '%1'", [$schema]));
        }
        $schema_path = $package . '/' . $matches['path'];
        if (empty($package) || !file_exists($schema_path)) {
            throw new Not_Found_Exception(new Phrase("Could not locate schema: '%1' at '%2'", [$schema, $schema_path]));
        }
        return $schema_path;
    }
    /**
     * Callback registered for libxml to resolve URN to the file path
     *
     * @param string $public
     * @param string $system
     * @param array $context
     * @return resource
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function register_entity_loader($public, $system, $context)
    {
        if ($system && strpos($system, 'urn:') === 0) {
            $file_path = $this->get_real_path($system);
        } else if (file_exists($system)) {
            $file_path = $system;
        } else {
            throw new Localized_Exception(new Phrase("File '%system' cannot be found", ['system' => $system]));
        }
        return fopen($file_path, 'r');
    }
}