<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller\Noroute;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Noroute application handler
     *
     * @return void
     */
    public function execute()
    {
        $status = $this->get_request()->get_param('__status__');
        if (!$status instanceof \Magento\Framework\Data_Object) {
            $status = new \Magento\Framework\Data_Object();
        }
        $this->_event_manager->dispatch('controller_action_noroute', ['action' => $this, 'status' => $status]);
        if ($status->get_loaded() !== true || $status->get_forwarded() === true) {
            $this->_view->load_layout(['default', 'noroute']);
            $this->_view->render_layout();
        } else {
            $status->set_forwarded(true);
            $request = $this->get_request();
            $request->init_forward();
            $request->set_params(['__status__' => $status]);
            $request->set_controller_name($status->get_forward_controller());
            $request->set_module_name($status->get_forward_module());
            $request->set_action_name($status->get_forward_action())->set_dispatched(false);
        }
    }
}