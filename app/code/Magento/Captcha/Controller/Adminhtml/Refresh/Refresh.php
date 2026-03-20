<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Controller\Adminhtml\Refresh;

class Refresh extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $captcha_helper;
    /**
     * Refresh constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Captcha\Helper\Data $captcha_helper, \Magento\Framework\Serialize\Serializer\Json $serializer)
    {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->captcha_helper = $captcha_helper;
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $form_id = $this->get_request()->get_post('formId');
        $captcha_model = $this->captcha_helper->get_captcha($form_id);
        $this->_view->get_layout()->create_block($captcha_model->get_block_name())->set_form_id($form_id)->set_is_ajax(true)->to_html();
        $this->get_response()->represent_json($this->serializer->serialize(['imgSrc' => $captcha_model->get_img_src()]));
        $this->_action_flag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _is_allowed()
    {
        return true;
    }
}