<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\Config_Type_Interface;
use Magento\Framework\App\Config\Scope_Code_Resolver;
use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 */
class Config implements Scope_Config_Interface
{
    /**
     * Config cache tag
     */
    public const CACHE_TAG = 'CONFIG';
    /**
     * @var ScopeCodeResolver
     */
    private $scope_code_resolver;
    /**
     * @var ConfigTypeInterface[]
     */
    private $types;
    /**
     * Config constructor.
     *
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param array $types
     */
    public function __construct(Scope_Code_Resolver $scope_code_resolver, array $types = [])
    {
        $this->scope_code_resolver = $scope_code_resolver;
        $this->types = $types;
    }
    /**
     * @inheritDoc
     */
    public function get_value($path = null, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null)
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $config_path = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scope_code) || $scope_code === null) {
                $scope_code = $this->scope_code_resolver->resolve($scope, $scope_code);
            } elseif ($scope_code instanceof \Magento\Framework\App\Scope_Interface) {
                $scope_code = $scope_code->get_code();
            }
            if ($scope_code) {
                $config_path .= '/' . $scope_code;
            }
        }
        if ($path) {
            $config_path .= '/' . $path;
        }
        return $this->get('system', $config_path);
    }
    /**
     * @inheritDoc
     */
    public function is_set_flag($path, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null)
    {
        return !!$this->get_value($path, $scope, $scope_code);
    }
    /**
     * Invalidate cache by type
     *
     * Clean scopeCodeResolver
     *
     * @return void
     */
    public function clean()
    {
        foreach ($this->types as $type) {
            $type->clean();
        }
        $this->scope_code_resolver->clean();
    }
    /**
     * Retrieve configuration.
     *
     * ('modules') - modules status configuration data
     * ('scopes', 'websites/base') - base website data
     * ('scopes', 'stores/default') - default store data
     *
     * ('system', 'default/web/seo/use_rewrites') - default system configuration data
     * ('system', 'websites/base/web/seo/use_rewrites') - 'base' website system configuration data
     *
     * ('i18n', 'default/en_US') - translations for default store and 'en_US' locale
     *
     * @param string $configType
     * @param string|null $path
     * @param mixed|null $default
     * @return array
     */
    public function get($config_type, $path = '', $default = null)
    {
        $result = null;
        if (isset($this->types[$config_type])) {
            $result = $this->types[$config_type]->get($path);
        }
        return $result !== null ? $result : $default;
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}