<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
class Translate extends \Magento\Backend\App\Action implements Http_Post_Action_Interface
{
    /**
     * @var \Magento\Framework\Translate\Inline\ParserInterface
     */
    protected $inline_parser;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $result_json_factory;
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::content_translation';
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Translate\Inline\ParserInterface $inlineParser
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(Action\Context $context, \Magento\Framework\Translate\Inline\Parser_Interface $inline_parser, \Magento\Framework\Controller\Result\Json_Factory $result_json_factory)
    {
        parent::__construct($context);
        $this->result_json_factory = $result_json_factory;
        $this->inline_parser = $inline_parser;
    }
    /**
     * Ajax action for inline translation
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $translate = (array) $this->get_request()->get_post('translate');
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $result_json = $this->result_json_factory->create();
        try {
            $response = $this->inline_parser->process_ajax_post($translate);
        } catch (\Exception $e) {
            $response = ['error' => 'true', 'message' => $e->get_message()];
        }
        $this->_action_flag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $result_json->set_data($response);
    }
}