<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Data_Provider\Bulk;

use Magento\Framework\App\Request_Interface;
/**
 * Class IdentifierResolver
 */
class Identifier_Resolver
{
    public function __construct(private readonly Request_Interface $request)
    {
    }
    /**
     * @return null|string
     */
    public function execute()
    {
        return $this->request->get_param('uuid');
    }
}