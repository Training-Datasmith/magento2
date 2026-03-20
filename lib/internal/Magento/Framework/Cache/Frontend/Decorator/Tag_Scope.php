<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\Cache_Constants;
/**
 * Cache frontend decorator that limits the cleaning scope within a tag
 *
 * @api
 * @since 100.0.2
 */
class Tag_Scope extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    private $_tag;
    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @param string $tag Cache tag name
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $frontend, $tag)
    {
        parent::__construct($frontend);
        $this->_tag = $tag;
    }
    /**
     * Retrieve cache tag name
     *
     * @return string
     */
    public function get_tag()
    {
        return $this->_tag;
    }
    /**
     * @inheritDoc
     *
     * Enforce marking with a tag
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        $tags[] = $this->get_tag();
        return parent::save($data, $identifier, $tags, $life_time);
    }
    /**
     * @inheritDoc
     *
     * Limit the cleaning scope within a tag
     *
     * This matches Zend cache implementation exactly
     * (vendor/magento/framework/Cache/Frontend/Decorator/TagScope.php)
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        if ($mode == Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG) {
            // Same as Zend: Loop through tags and clean each with scope
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(Cache_Constants::CLEANING_MODE_MATCHING_TAG, [$tag, $this->get_tag()])) {
                    $result = true;
                }
            }
        } else {
            if ($mode == Cache_Constants::CLEANING_MODE_ALL) {
                $mode = Cache_Constants::CLEANING_MODE_MATCHING_TAG;
                $tags = [$this->get_tag()];
            } else {
                $tags[] = $this->get_tag();
            }
            $result = parent::clean($mode, $tags);
        }
        return $result;
    }
}