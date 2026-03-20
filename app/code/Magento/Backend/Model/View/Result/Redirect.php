<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Backend\App\Abstract_Action;
use Magento\Backend\Model\Session;
use Magento\Framework\App;
use Magento\Framework\App\Action_Flag;
use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
/**
 * @api
 * @since 100.0.2
 */
class Redirect extends \Magento\Framework\Controller\Result\Redirect
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $action_flag;
    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param Session $session
     * @param ActionFlag $actionFlag
     */
    public function __construct(App\Response\Redirect_Interface $redirect, \Magento\Backend\Model\Url_Interface $url_builder, Session $session, Action_Flag $action_flag)
    {
        $this->session = $session;
        $this->action_flag = $action_flag;
        parent::__construct($redirect, $url_builder);
    }
    /**
     * Set referer url or dashboard if referer does not exist
     *
     * @return $this
     */
    public function set_referer_or_base_url()
    {
        $this->url = $this->redirect->get_redirect_url($this->url_builder->get_url($this->url_builder->get_startup_page_url()));
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function render(Http_Response_Interface $response)
    {
        $this->session->set_is_url_notice($this->action_flag->get('', Abstract_Action::FLAG_IS_URLS_CHECKED));
        return parent::render($response);
    }
}