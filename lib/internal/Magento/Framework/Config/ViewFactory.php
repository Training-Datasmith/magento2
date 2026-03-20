<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Object_Manager_Interface;
/**
 * @api
 */
class View_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $object_manager;
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create new view object
     *
     * @param array $arguments
     * @return \Magento\Framework\Config\View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $arguments = [])
    {
        $view_config_arguments = [];
        if (isset($arguments['themeModel']) && isset($arguments['area'])) {
            if (!$arguments['themeModel'] instanceof \Magento\Framework\View\Design\Theme_Interface) {
                throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('%1 doesn\'t implement ThemeInterface', [$arguments['themeModel']]));
            }
            /** @var \Magento\Theme\Model\View\Design $design */
            $design = $this->object_manager->create(\Magento\Theme\Model\View\Design::class);
            $design->set_design_theme($arguments['themeModel'], $arguments['area']);
            /** @var \Magento\Framework\Config\FileResolver $fileResolver */
            $file_resolver = $this->object_manager->create(\Magento\Framework\Config\File_Resolver::class, ['designInterface' => $design]);
            $view_config_arguments['fileResolver'] = $file_resolver;
        }
        return $this->object_manager->create(\Magento\Framework\Config\View::class, $view_config_arguments);
    }
}