<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object;

use Ramsey\Uuid\Uuid;
/**
 * Class IdentityService
 */
class Identity_Service implements Identity_Generator_Interface
{
    /**
     * @var \Ramsey\Uuid\UuidFactoryInterface
     */
    private $uuid_factory;
    /**
     * IdentityService constructor.
     */
    public function __construct()
    {
        $this->uuid_factory = new \Ramsey\Uuid\Uuid_Factory();
    }
    /**
     * @inheritDoc
     */
    public function generate_id()
    {
        $uuid = $this->uuid_factory->uuid4();
        return $uuid->to_string();
    }
    /**
     * @inheritDoc
     */
    public function generate_id_for_data($data)
    {
        $uuid = $this->uuid_factory->uuid3(Uuid::NAMESPACE_DNS, $data);
        return $uuid->to_string();
    }
}