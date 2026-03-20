<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer;
use Magento\Framework\Data_Object;
use Magento\Framework\Notification\Message_Interface;
/**
 * Renderer class for severity in the admin notifications grid
 */
class Severity extends Abstract_Renderer
{
    public function __construct(Context $context, protected \Magento\Admin_Notification\Model\Inbox $_notice, array $data = [])
    {
        parent::__construct($context, $data);
    }
    /**
     * Renders grid column
     */
    public function render(Data_Object $row): string
    {
        $class = '';
        $value = '';
        $column = $this->get_column();
        $index = $column->get_index();
        switch ($row->get_data($index)) {
            case Message_Interface::SEVERITY_CRITICAL:
                $class = 'critical';
                $value = $this->_notice->get_severities(Message_Interface::SEVERITY_CRITICAL);
                break;
            case Message_Interface::SEVERITY_MAJOR:
                $class = 'major';
                $value = $this->_notice->get_severities(Message_Interface::SEVERITY_MAJOR);
                break;
            case Message_Interface::SEVERITY_MINOR:
                $class = 'minor';
                $value = $this->_notice->get_severities(Message_Interface::SEVERITY_MINOR);
                break;
            case Message_Interface::SEVERITY_NOTICE:
                $class = 'notice';
                $value = $this->_notice->get_severities(Message_Interface::SEVERITY_NOTICE);
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}