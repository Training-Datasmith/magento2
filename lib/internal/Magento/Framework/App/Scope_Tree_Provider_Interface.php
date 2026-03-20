<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeTreeProviderInterface
 *
 * @api
 */
interface Scope_Tree_Provider_Interface
{
    /**
     * Return tree of scopes like:
     * [
     *      'scope' => 'default',
     *      'scope_id' => null,
     *      'scopes' => [
     *          [
     *              'scope' => 'website',
     *              'scope_id' => 1,
     *              'scopes' => [
     *                  ...
     *              ],
     *          ],
     *          ...
     *      ],
     * ]
     *
     * @return array
     */
    public function get();
}