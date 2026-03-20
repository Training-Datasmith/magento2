<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Theme configuration files handler
 */
namespace Magento\Framework\Config;

/**
 * @api
 * @since 100.0.2
 */
class Theme
{
    /**
     * Is used for separation path of themes
     */
    public const THEME_PATH_SEPARATOR = '/';
    /**
     * Data extracted from the configuration file
     *
     * @var array
     */
    protected $_data;
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urn_resolver;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param string $configContent
     */
    public function __construct(\Magento\Framework\Config\Dom\Urn_Resolver $urn_resolver, $config_content = null)
    {
        $this->urn_resolver = $urn_resolver;
        $this->_data = $this->_extract_data($config_content);
    }
    /**
     * Get absolute path to theme.xsd
     *
     * @return string
     */
    public function get_schema_file()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:Config/etc/theme.xsd');
    }
    /**
     * Extract configuration data from theme.xml
     *
     * @param string $configContent
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _extract_data($config_content)
    {
        $data = ['title' => null, 'media' => null, 'parent' => null];
        if (!empty($config_content)) {
            $dom = new \Dom_Document();
            $dom->load_xml($config_content);
            // todo: validation of the document
            /** @var $themeNode \DOMElement */
            $theme_node = $dom->get_elements_by_tag_name('theme')->item(0);
            $theme_title_node = $theme_node->get_elements_by_tag_name('title')->item(0);
            $data['title'] = $theme_title_node ? $theme_title_node->node_value : null;
            /** @var $mediaNode \DOMElement */
            $media_node = $theme_node->get_elements_by_tag_name('media')->item(0);
            $preview_image = $media_node ? $media_node->get_elements_by_tag_name('preview_image')->item(0)->node_value : '';
            $data['media']['preview_image'] = $preview_image;
            $theme_parent_node = $theme_node->get_elements_by_tag_name('parent')->item(0);
            $data['parent'] = $theme_parent_node ? $theme_parent_node->node_value : null;
        }
        return $data;
    }
    /**
     * Get title for specified theme and package code
     *
     * @return string
     */
    public function get_theme_title()
    {
        return $this->_data['title'];
    }
    /**
     * Get theme media data
     *
     * @return array
     */
    public function get_media()
    {
        return $this->_data['media'];
    }
    /**
     * Retrieve a parent theme code
     *
     * @return array|null
     */
    public function get_parent_theme()
    {
        $parent_theme = $this->_data['parent'];
        if (!$parent_theme) {
            return null;
        }
        return explode(self::THEME_PATH_SEPARATOR, $parent_theme);
    }
}