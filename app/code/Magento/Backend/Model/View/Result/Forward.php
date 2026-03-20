<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Backend\App\Abstract_Action;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Action_Flag;
use Magento\Framework\App\Request_Interface;
/**
 * @api
 * @since 100.0.2
 */
class Forward extends \Magento\Framework\Controller\Result\Forward
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
     * @param RequestInterface $request
     * @param Session $session
     * @param ActionFlag $actionFlag
     */
    public function __construct(Request_Interface $request, Session $session, Action_Flag $action_flag)
    {
        $this->session = $session;
        $this->action_flag = $action_flag;
        parent::__construct($request);
    }
    /**
     * @param string $action
     * @return $this
     */
    public function forward($action)
    {
        $this->session->set_is_url_notice($this->action_flag->get('', Abstract_Action::FLAG_IS_URLS_CHECKED));
        return parent::forward($action);
    }
}