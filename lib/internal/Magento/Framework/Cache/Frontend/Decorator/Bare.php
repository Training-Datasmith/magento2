<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
/**
 * Cache frontend decorator that attaches no additional responsibility to a decorated instance.
 * To be used as an ancestor for concrete decorators to conveniently override only methods of interest.
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\Cache_Constants;
class Bare implements \Magento\Framework\Cache\Frontend_Interface
{
    /**
     * Cache frontend instance to delegate actual cache operations to
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_frontend;
    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $frontend)
    {
        $this->_frontend = $frontend;
    }
    /**
     * Set frontend
     *
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @return $this
     */
    protected function set_frontend(\Magento\Framework\Cache\Frontend_Interface $frontend)
    {
        $this->_frontend = $frontend;
        return $this;
    }
    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    protected function _get_frontend()
    {
        return $this->_frontend;
    }
    /**
     * @inheritdoc
     */
    public function test($identifier)
    {
        return $this->_get_frontend()->test($identifier);
    }
    /**
     * @inheritdoc
     */
    public function load($identifier)
    {
        return $this->_get_frontend()->load($identifier);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        return $this->_get_frontend()->save($data, $identifier, $tags, $life_time);
    }
    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        return $this->_get_frontend()->remove($identifier);
    }
    /**
     * @inheritdoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        return $this->_get_frontend()->clean($mode, $tags);
    }
    /**
     * @inheritdoc
     */
    public function get_backend()
    {
        return $this->_get_frontend()->get_backend();
    }
    /**
     * @inheritdoc
     */
    public function get_low_level_frontend()
    {
        return $this->_get_frontend()->get_low_level_frontend();
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