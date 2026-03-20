<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\Setup\Backend_Frontname_Generator;
/**
 * A model for determining information about setup application
 */
class Setup_Info
{
    /**#@+
     * Initialization parameters for redirecting if the application is not installed
     */
    public const PARAM_NOT_INSTALLED_URL_PATH = 'MAGE_NOT_INSTALLED_URL_PATH';
    public const PARAM_NOT_INSTALLED_URL = 'MAGE_NOT_INSTALLED_URL';
    /**#@-*/
    /**
     * Default path relative to the project root
     */
    public const DEFAULT_PATH = 'setup';
    /**
     * Environment variables
     *
     * @var array
     */
    private $server;
    /**
     * Current document root directory
     *
     * @var string
     */
    private $doc_root;
    /**
     * Project root directory
     *
     * @var string
     */
    private $project_root;
    /**
     * Constructor
     *
     * @param array $server
     * @param string $projectRoot
     * @throws \InvalidArgumentException
     */
    public function __construct($server, $project_root = '')
    {
        $this->server = $server;
        if (empty($server['DOCUMENT_ROOT'])) {
            throw new \InvalidArgumentException('DOCUMENT_ROOT variable is unavailable.');
        }
        $this->doc_root = rtrim(str_replace('\\', '/', $server['DOCUMENT_ROOT']), '/');
        $this->project_root = $project_root ?: $this->detect_project_root();
        $this->project_root = str_replace('\\', '/', $this->project_root);
    }
    /**
     * Automatically detects project root from current environment
     *
     * Assumptions:
     * if the current setup application relative path is at the end of script path, then it is setup application
     * otherwise it is the "main" application
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function detect_project_root()
    {
        if (empty($this->server['SCRIPT_FILENAME'])) {
            throw new \InvalidArgumentException('Project root cannot be automatically detected.');
        }
        $haystack = str_replace('\\', '/', dirname($this->server['SCRIPT_FILENAME']));
        $needle = '/' . $this->get_path();
        $is_setup_app = preg_match('/^(.+?)' . preg_quote($needle, '/') . '$/', $haystack, $matches);
        if ($is_setup_app) {
            return $matches[1];
        }
        return $haystack;
    }
    /**
     * Gets setup application URL
     *
     * @return string
     */
    public function get_url()
    {
        if (isset($this->server[self::PARAM_NOT_INSTALLED_URL])) {
            return $this->server[self::PARAM_NOT_INSTALLED_URL];
        }
        return Request\Http::get_distro_base_url_path($this->server) . $this->get_path() . '/';
    }
    /**
     * Gets the "main" application URL
     *
     * @return string
     */
    public function get_project_url()
    {
        $is_project_in_doc_root = false !== strpos($this->project_root . '/', $this->doc_root . '/');
        if (empty($this->server['HTTP_HOST'])) {
            return '';
        } elseif (!$is_project_in_doc_root) {
            return 'http://' . $this->server['HTTP_HOST'] . '/';
        }
        return 'http://' . $this->server['HTTP_HOST'] . substr($this->project_root . '/', strlen($this->doc_root));
    }
    /**
     * Get the admin area path
     *
     * @return string
     */
    public function get_project_admin_path()
    {
        return Backend_Frontname_Generator::generate();
    }
    /**
     * Gets setup application directory path in the filesystem
     *
     * @param string $projectRoot
     * @return string
     */
    public function get_dir($project_root)
    {
        return rtrim($project_root, '/') . '/' . $this->get_path();
    }
    /**
     * Checks if the setup application is available in current document root
     *
     * @return bool
     */
    public function is_available()
    {
        $setup_dir = $this->get_dir($this->project_root);
        $is_sub_dir = false !== strpos($setup_dir . '/', $this->doc_root . '/');
        // Setup is not accessible from pub folder
        $setup_dir = rtrim($setup_dir, '/');
        $last_occurrence = strrpos($setup_dir, '/pub/setup');
        if (false !== $last_occurrence) {
            $setup_dir = substr_replace($setup_dir, '/setup', $last_occurrence, strlen('/pub/setup'));
        }
        return $is_sub_dir && realpath($setup_dir);
    }
    /**
     * Gets relative path to setup application
     *
     * @return string
     */
    private function get_path()
    {
        if (isset($this->server[self::PARAM_NOT_INSTALLED_URL_PATH])) {
            return trim($this->server[self::PARAM_NOT_INSTALLED_URL_PATH], '/');
        }
        return self::DEFAULT_PATH;
    }
}