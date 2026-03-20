<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Controller\Refresh;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\Controller\Result\Json_Factory as JsonResultFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Layout_Interface;
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 */
class Index extends Action implements Http_Post_Action_Interface
{
    /**
     * @var CaptchaHelper
     */
    private $captcha_helper;
    /**
     * @var JsonSerializer
     */
    private $serializer;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var LayoutInterface
     */
    private $layout;
    /**
     * @var JsonResultFactory
     */
    private $json_result_factory;
    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param JsonResultFactory $jsonFactory
     * @param CaptchaHelper $captchaHelper
     * @param LayoutInterface $layout
     * @param JsonSerializer $serializer
     */
    public function __construct(Context $context, Request_Interface $request, Json_Result_Factory $json_factory, Captcha_Helper $captcha_helper, Layout_Interface $layout, Json_Serializer $serializer)
    {
        parent::__construct($context);
        $this->request = $request;
        $this->json_result_factory = $json_factory;
        $this->captcha_helper = $captcha_helper;
        $this->layout = $layout;
        $this->serializer = $serializer;
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $form_id = $this->get_request_form_id();
        $captcha_model = $this->captcha_helper->get_captcha($form_id);
        $captcha_model->generate();
        $block = $this->layout->create_block($captcha_model->get_block_name());
        $block->set_form_id($form_id)->set_is_ajax(true)->to_html();
        $result = $this->json_result_factory->create();
        return $result->set_data(['imgSrc' => $captcha_model->get_img_src()]);
    }
    /**
     * Returns requested Form ID
     *
     * @return string|null
     */
    private function get_request_form_id(): ?string
    {
        $form_id = $this->request->get_post('formId');
        if (null === $form_id) {
            $params = [];
            $content = $this->request->get_content();
            if ($content) {
                $params = $this->serializer->unserialize($content);
            }
            $form_id = $params['formId'] ?? null;
        }
        return $form_id !== null ? (string) $form_id : null;
    }
}