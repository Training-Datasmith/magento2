<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Model\Checkout;

/**
 * Configuration provider for Captcha rendering.
 */
class Config_Provider implements \Magento\Checkout\Model\Config_Provider_Interface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store_manager;
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $captcha_data;
    /**
     * @var array
     */
    protected $form_ids;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $formIds
     */
    public function __construct(\Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Captcha\Helper\Data $captcha_data, array $form_ids)
    {
        $this->store_manager = $store_manager;
        $this->captcha_data = $captcha_data;
        $this->form_ids = $form_ids;
    }
    /**
     * @inheritdoc
     */
    public function get_config()
    {
        $config = [];
        foreach ($this->form_ids as $form_id) {
            $config['captcha'][$form_id] = ['isCaseSensitive' => $this->is_case_sensitive($form_id), 'imageHeight' => $this->get_image_height($form_id), 'imageSrc' => $this->get_image_src($form_id), 'refreshUrl' => $this->get_refresh_url(), 'isRequired' => $this->is_required($form_id), 'timestamp' => time()];
        }
        return $config;
    }
    /**
     * Returns is captcha case sensitive
     *
     * @param string $formId
     * @return bool
     */
    protected function is_case_sensitive($form_id)
    {
        return (bool) $this->get_captcha_model($form_id)->is_case_sensitive();
    }
    /**
     * Returns captcha image height
     *
     * @param string $formId
     * @return int
     */
    protected function get_image_height($form_id)
    {
        return $this->get_captcha_model($form_id)->get_height();
    }
    /**
     * Returns captcha image source path
     *
     * @param string $formId
     * @return string
     */
    protected function get_image_src($form_id)
    {
        if ($this->is_required($form_id)) {
            $captcha = $this->get_captcha_model($form_id);
            $captcha->generate();
            return $captcha->get_img_src();
        }
        return '';
    }
    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    protected function get_refresh_url()
    {
        $store = $this->store_manager->get_store();
        return $store->get_url('captcha/refresh', ['_secure' => $store->is_currently_secure()]);
    }
    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param string $formId
     * @return bool
     */
    protected function is_required($form_id)
    {
        return (bool) $this->get_captcha_model($form_id)->is_required();
    }
    /**
     * Return captcha model for specified form
     *
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     */
    protected function get_captcha_model($form_id)
    {
        return $this->captcha_data->get_captcha($form_id);
    }
}