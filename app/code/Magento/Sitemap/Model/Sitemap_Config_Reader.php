<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SitemapConfigReader implements SitemapConfigReaderInterface
{
    /**
     * Config path to sitemap valid paths
     */
    public const XML_PATH_SITEMAP_VALID_PATHS = 'sitemap/file/valid_paths';

    /**
     * Config path to valid file paths
     */
    public const XML_PATH_PUBLIC_FILES_VALID_PATHS = 'general/file/public_files_valid_paths';

    /**#@+
     * Limits xpath config settings
     */
    public const XML_PATH_MAX_LINES = 'sitemap/limit/max_lines';
    public const XML_PATH_MAX_FILE_SIZE = 'sitemap/limit/max_file_size';
    /**#@-*/

    /**
     * Search Engine Submission Settings
     */
    public const XML_PATH_SUBMISSION_ROBOTS = 'sitemap/search_engines/submission_robots';

    /**
     * Include product image setting
     */
    public const XML_PATH_PRODUCT_IMAGES_INCLUDE = 'sitemap/product/image_include';

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Sitemap Config Reader constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableSubmissionRobots($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBMISSION_ROBOTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumFileSize($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumLinesNumber($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_LINES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProductImageIncludePolicy($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValidPaths()
    {
        return array_merge(
            $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE),
            $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE)
        );
    }
}
