<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Response;

/**
 * Validates payload of CardinalCommerce response JWT.
 *
 * @api
 */
interface Jwt_Payload_Validator_Interface
{
    /**
     * Validates token payload.
     *
     * @param array $jwtPayload
     * @return bool
     */
    public function validate(array $jwt_payload);
}