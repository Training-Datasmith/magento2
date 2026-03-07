<?php

declare(strict_types=1);

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Notification\MessageInterface;

/**
 * Renderer class for severity in the admin notifications grid
 */
class Severity extends AbstractRenderer
{
    public function __construct(Context $context, protected \Magento\AdminNotification\Model\Inbox $_notice, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     */
    public function render(DataObject $row): string
    {
        $class = '';
        $value = '';

        $column = $this->getColumn();
        $index  = $column->getIndex();
        switch ($row->getData($index)) {
            case MessageInterface::SEVERITY_CRITICAL:
                $class = 'critical';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_CRITICAL);
                break;
            case MessageInterface::SEVERITY_MAJOR:
                $class = 'major';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_MAJOR);
                break;
            case MessageInterface::SEVERITY_MINOR:
                $class = 'minor';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_MINOR);
                break;
            case MessageInterface::SEVERITY_NOTICE:
                $class = 'notice';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_NOTICE);
                break;
        }

        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
