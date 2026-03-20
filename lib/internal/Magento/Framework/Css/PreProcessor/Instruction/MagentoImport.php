<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Css\Pre_Processor\Instruction;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Css\Pre_Processor\Error_Handler_Interface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\View\Asset\File\Fallback_Context;
use Magento\Framework\View\Asset\Local_Interface;
use Magento\Framework\View\Asset\Pre_Processor\Chain;
use Magento\Framework\View\Asset\Pre_Processor_Interface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\List_Interface as ThemeListInterface;
use Magento\Framework\View\Design\Theme\Theme_Provider_Interface;
use Magento\Framework\View\Design\Theme_Interface;
use Magento\Framework\View\Design_Interface;
use Magento\Framework\View\File\Collector_Interface;
/**
 * @magento_import instruction preprocessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Must be deleted after moving themeProvider to construct
 */
class Magento_Import implements Pre_Processor_Interface, Reset_After_Request_Interface
{
    /**
     * PCRE pattern that matches @magento_import instruction
     */
    public const REPLACE_PATTERN = '#//@magento_import(?P<reference>\s+\(reference\))?\s+[\'\"](?P<path>(?![/\\\\]|\w:[/\\\\])[^\"\']+)[\'\"]\s*?;#';
    private const CONFIG_PATH_SCD_ONLY_ENABLED_MODULES = 'static_content_only_enabled_modules';
    /**
     * @var DesignInterface
     */
    protected $design;
    /**
     * @var CollectorInterface
     */
    protected $file_source;
    /**
     * @var ErrorHandlerInterface
     */
    protected $error_handler;
    /**
     * @var AssetRepository
     */
    protected $asset_repo;
    /**
     * @var ThemeListInterface
     * @deprecated 100.0.2
     * @see not used
     */
    protected $theme_list;
    /**
     * @var ThemeProviderInterface|null
     */
    private $theme_provider;
    /**
     * @var DeploymentConfig
     */
    private Deployment_Config $deployment_config;
    /**
     * @var ModuleManager
     */
    private Module_Manager $module_manager;
    /**
     * @param DesignInterface $design
     * @param CollectorInterface $fileSource
     * @param ErrorHandlerInterface $errorHandler
     * @param AssetRepository $assetRepo
     * @param ThemeListInterface $themeList
     * @param DeploymentConfig|null $deploymentConfig
     * @param ModuleManager|null $moduleManager
     */
    public function __construct(Design_Interface $design, Collector_Interface $file_source, Error_Handler_Interface $error_handler, Asset_Repository $asset_repo, Theme_List_Interface $theme_list, ?Deployment_Config $deployment_config = null, ?Module_Manager $module_manager = null)
    {
        $this->design = $design;
        $this->file_source = $file_source;
        $this->error_handler = $error_handler;
        $this->asset_repo = $asset_repo;
        $this->theme_list = $theme_list;
        $this->deployment_config = $deployment_config ?? Object_Manager::get_instance()->get(Deployment_Config::class);
        $this->module_manager = $module_manager ?? Object_Manager::get_instance()->get(Module_Manager::class);
    }
    /**
     * @inheritDoc
     */
    public function process(Chain $chain)
    {
        $asset = $chain->get_asset();
        $replace_callback = function ($match_content) use ($asset) {
            return $this->replace($match_content, $asset);
        };
        $chain->set_content(preg_replace_callback(self::REPLACE_PATTERN, $replace_callback, $chain->get_content()));
    }
    /**
     * Replace @magento_import to @import instructions
     *
     * @param array $matchedContent
     * @param LocalInterface $asset
     * @return string
     */
    protected function replace(array $matched_content, Local_Interface $asset)
    {
        $imports_content = '';
        try {
            $matched_file_id = $matched_content['path'];
            $is_reference = !empty($matched_content['reference']);
            $related_asset = $this->asset_repo->create_related($matched_file_id, $asset);
            $resolved_path = $related_asset->get_file_path();
            $import_files = $this->file_source->get_files($this->get_theme($related_asset), $resolved_path);
            $deploy_only_enabled = $this->has_enabled_flag_deploy_enabled_modules();
            /** @var $importFile \Magento\Framework\View\File */
            foreach ($import_files as $import_file) {
                $module_name = $import_file->get_module();
                $reference_string = $is_reference ? '(reference) ' : '';
                if ($module_name) {
                    if (!$deploy_only_enabled || $this->module_manager->is_enabled($module_name)) {
                        $imports_content .= "@import {$reference_string}'{$module_name}::{$resolved_path}';\n";
                    }
                } else {
                    $imports_content .= "@import {$reference_string}'{$matched_file_id}';\n";
                }
            }
        } catch (\LogicException $e) {
            $this->error_handler->process_exception($e);
        }
        return $imports_content;
    }
    /**
     * Retrieve flag deploy enabled modules
     *
     * @return bool
     */
    private function has_enabled_flag_deploy_enabled_modules(): bool
    {
        return (bool) $this->deployment_config->get(self::CONFIG_PATH_SCD_ONLY_ENABLED_MODULES);
    }
    /**
     * Get theme model based on the information from asset
     *
     * @param LocalInterface $asset
     * @return ThemeInterface
     */
    protected function get_theme(Local_Interface $asset)
    {
        $context = $asset->get_context();
        if ($context instanceof Fallback_Context) {
            return $this->get_theme_provider()->get_theme_by_full_path($context->get_area_code() . '/' . $context->get_theme_path());
        }
        return $this->design->get_design_theme();
    }
    /**
     * Gets themeProvider, lazy loading it when needed
     *
     * @return ThemeProviderInterface
     */
    private function get_theme_provider(): Theme_Provider_Interface
    {
        if (null === $this->theme_provider) {
            $this->theme_provider = Object_Manager::get_instance()->get(Theme_Provider_Interface::class);
        }
        return $this->theme_provider;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->theme_provider = null;
    }
}