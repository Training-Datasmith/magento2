<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Model\Customer\Plugin;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\Controller\Result\Json_Factory;
use Magento\Framework\Session\Session_Manager_Interface;
/**
 * Around plugin for login action.
 */
class Ajax_Login
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session_manager;
    /**
     * @var JsonFactory
     */
    protected $result_json_factory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;
    /**
     * @var array
     */
    protected $form_ids;
    /**
     * @param CaptchaHelper $helper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $resultJsonFactory
     * @param array $formIds
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(Captcha_Helper $helper, Session_Manager_Interface $session_manager, Json_Factory $result_json_factory, array $form_ids, ?\Magento\Framework\Serialize\Serializer\Json $serializer = null)
    {
        $this->helper = $helper;
        $this->session_manager = $session_manager;
        $this->result_json_factory = $result_json_factory;
        $this->serializer = $serializer ?: \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->form_ids = $form_ids;
    }
    /**
     * Check captcha data on login action.
     *
     * @param \Magento\Customer\Controller\Ajax\Login $subject
     * @param \Closure $proceed
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function around_execute(\Magento\Customer\Controller\Ajax\Login $subject, \Closure $proceed)
    {
        $captcha_form_id_field = 'captcha_form_id';
        $captcha_input_name = 'captcha_string';
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $subject->get_request();
        $login_params = [];
        $content = $request->get_content();
        if ($content) {
            $login_params = $this->serializer->unserialize($content);
        }
        $username = $login_params['username'] ?? null;
        $captcha_string = $login_params[$captcha_input_name] ?? null;
        $login_form_id = $login_params[$captcha_form_id_field] ?? null;
        if (!in_array($login_form_id, $this->form_ids) && $this->helper->get_captcha($login_form_id)->is_required($username)) {
            return $this->return_json_error(__('Provided form does not exist'));
        }
        foreach ($this->form_ids as $form_id) {
            if ($form_id === $login_form_id) {
                $captcha_model = $this->helper->get_captcha($form_id);
                if ($captcha_model->is_required($username)) {
                    if (!$captcha_model->is_correct($captcha_string)) {
                        $this->session_manager->set_username($username);
                        $captcha_model->log_attempt($username);
                        return $this->return_json_error(__('Incorrect CAPTCHA'));
                    }
                }
                $captcha_model->log_attempt($username);
            }
        }
        return $proceed();
    }
    /**
     * Format JSON response.
     *
     * @param \Magento\Framework\Phrase $phrase
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function return_json_error(\Magento\Framework\Phrase $phrase): \Magento\Framework\Controller\Result\Json
    {
        $result_json = $this->result_json_factory->create();
        return $result_json->set_data(['errors' => true, 'message' => $phrase]);
    }
}