<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Cache;

/**
 * @api
 * @since 100.0.2
 */
class Additional extends \Magento\Backend\Block\Template
{
    /**
     * Check if application is in production mode
     *
     * @return bool
     */
    public function is_in_production_mode()
    {
        return $this->_app_state->get_mode() === \Magento\Framework\App\State::MODE_PRODUCTION;
    }
    /**
     * @return string
     */
    public function get_clean_images_url()
    {
        return $this->get_url('*/*/cleanImages');
    }
    /**
     * @return string
     */
    public function get_clean_media_url()
    {
        return $this->get_url('*/*/cleanMedia');
    }
    /**
     * @return string
     */
    public function get_clean_static_files_url()
    {
        return $this->get_url('*/*/cleanStaticFiles');
    }
}