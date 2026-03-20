<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Customer_Data;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\Default_Model;
use Magento\Customer\Customer_Data\Section_Source_Interface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data_Object;
/**
 * Captcha section.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Captcha extends Data_Object implements Section_Source_Interface
{
    /**
     * @var array
     */
    private $form_ids;
    /**
     * @var CaptchaHelper
     */
    private $helper;
    /**
     * @var CustomerSession
     */
    private $customer_session;
    /**
     * @param CaptchaHelper $helper
     * @param array $formIds
     * @param array $data
     * @param CustomerSession|null $customerSession
     */
    public function __construct(Captcha_Helper $helper, array $form_ids, array $data = [], ?Customer_Session $customer_session = null)
    {
        $this->helper = $helper;
        $this->form_ids = $form_ids;
        parent::__construct($data);
        $this->customer_session = $customer_session ?? Object_Manager::get_instance()->get(Customer_Session::class);
    }
    /**
     * @inheritdoc
     */
    public function get_section_data(): array
    {
        $data = [];
        foreach ($this->form_ids as $form_id) {
            /** @var DefaultModel $captchaModel */
            $captcha_model = $this->helper->get_captcha($form_id);
            $login = '';
            if ($this->customer_session->is_logged_in()) {
                $login = $this->customer_session->get_customer_data()->get_email();
            }
            $required = $captcha_model->is_required($login);
            $data[$form_id] = ['isRequired' => $required, 'timestamp' => time()];
        }
        return $data;
    }
}