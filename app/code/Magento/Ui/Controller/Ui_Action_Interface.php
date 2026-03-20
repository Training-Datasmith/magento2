<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Controller;

/**
 * Interface UiActionInterface
 *
 * @api
 */
interface UiActionInterface
{
    /**
     * Execute action
     *
     * @return mixed
     */
    public function executeAjaxRequest();
}
