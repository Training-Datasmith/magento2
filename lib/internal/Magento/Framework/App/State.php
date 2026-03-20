<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Application state flags.
 * Can be used to retrieve current application mode and current area.
 *
 * Note: Area code communication and emulation will be removed from this class.
 *
 * @api
 * @since 100.0.2
 */
class State
{
    /**
     * Application run code
     */
    public const PARAM_MODE = 'MAGE_MODE';
    /**
     * Application mode
     *
     * @var string
     */
    protected $_app_mode;
    /**
     * Is downloader flag
     *
     * @var bool
     */
    protected $_is_downloader = false;
    /**
     * Update mode flag
     *
     * @var bool
     */
    protected $_update_mode = false;
    /**
     * Config scope model
     *
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_config_scope;
    /**
     * @var string
     */
    protected $_area_code;
    /**
     * Is area code being emulated
     *
     * @var bool
     */
    protected $_is_area_code_emulated = false;
    /**
     * @var AreaList
     */
    private $area_list;
    /**
     * Application modes
     */
    public const MODE_DEVELOPER = 'developer';
    public const MODE_PRODUCTION = 'production';
    public const MODE_DEFAULT = 'default';
    /**
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param string $mode
     * @throws \LogicException
     */
    public function __construct(\Magento\Framework\Config\Scope_Interface $config_scope, $mode = self::MODE_DEFAULT)
    {
        $this->_config_scope = $config_scope;
        switch ($mode) {
            case self::MODE_DEVELOPER:
            case self::MODE_PRODUCTION:
            case self::MODE_DEFAULT:
                $this->_app_mode = $mode;
                break;
            default:
                throw new \InvalidArgumentException("Unknown application mode: {$mode}");
        }
    }
    /**
     * Return current app mode
     *
     * @return string
     */
    public function get_mode()
    {
        return $this->_app_mode;
    }
    /**
     * Set is downloader flag
     *
     * @param bool $flag
     * @return void
     */
    public function set_is_downloader($flag = true)
    {
        $this->_is_downloader = $flag;
    }
    /**
     * Set area code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function set_area_code($code)
    {
        $this->check_area_code($code);
        if (isset($this->_area_code)) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Area code is already set'));
        }
        $this->_config_scope->set_current_scope($code);
        $this->_area_code = $code;
    }
    /**
     * Get area code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get_area_code()
    {
        if (!isset($this->_area_code)) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Area code is not set'));
        }
        return $this->_area_code;
    }
    /**
     * Checks whether area code is being emulated
     *
     * @return bool
     */
    public function is_area_code_emulated()
    {
        return $this->_is_area_code_emulated;
    }
    /**
     * Emulate callback inside some area code
     *
     * @param string $areaCode
     * @param callable $callback
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function emulate_area_code($area_code, $callback, $params = [])
    {
        $this->check_area_code($area_code);
        $current_area = $this->_area_code;
        $this->_area_code = $area_code;
        $this->_is_area_code_emulated = true;
        try {
            $result = call_user_func_array($callback, $params);
        } finally {
            $this->_area_code = $current_area;
            $this->_is_area_code_emulated = false;
        }
        return $result;
    }
    /**
     * Check that area code exists
     *
     * @param string $areaCode
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function check_area_code($area_code)
    {
        $area_codes = array_merge([Area::AREA_GLOBAL], $this->get_area_list_instance()->get_codes());
        if (!in_array($area_code, $area_codes)) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Area code "%1" does not exist', [$area_code]));
        }
    }
    /**
     * Get Instance of AreaList
     *
     * @return AreaList
     * @deprecated 101.0.0
     * @see Nothing
     */
    private function get_area_list_instance()
    {
        if ($this->area_list === null) {
            $this->area_list = Object_Manager::get_instance()->get(Area_List::class);
        }
        return $this->area_list;
    }
}