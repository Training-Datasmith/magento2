<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Developer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class WorkflowType
 *
 * @api
 * @since 100.0.2
 */
class WorkflowType implements ArrayInterface
{
    /**
     * Constant for
     */
    public const CONFIG_NAME_PATH = 'dev/front_end_development_workflow/type';

    /**
     * Constant for server side compilation workflow
     */
    public const SERVER_SIDE_COMPILATION = 'server_side_compilation';

    /**
     * Constant for client side compilation workflow
     */
    public const CLIENT_SIDE_COMPILATION = 'client_side_compilation';

    /**
     * Return list of Workflow types
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CLIENT_SIDE_COMPILATION, 'label' => __('Client side less compilation')],
            ['value' => self::SERVER_SIDE_COMPILATION, 'label' => __('Server side less compilation')],
        ];
    }
}
