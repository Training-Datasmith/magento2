<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Validator\Url_Key;

use Magento\Backend\App\Area\Front_Name_Resolver;
/**
 * Class FrontName validates if urlKey doesn't matches frontName
 */
class Front_Name implements Url_Key_Validator_Interface
{
    /**
     * @var FrontNameResolver
     */
    private $front_name_resolver;
    /**
     * @param FrontNameResolver $frontNameResolver
     */
    public function __construct(Front_Name_Resolver $front_name_resolver)
    {
        $this->front_name_resolver = $front_name_resolver;
    }
    /**
     * @inheritDoc
     */
    public function validate(string $url_key): array
    {
        $errors = [];
        $front_name = $this->front_name_resolver->get_front_name();
        if ($url_key == $front_name) {
            $errors[] = __('URL key "%1" matches a reserved endpoint name (%2). Use another URL key.', $url_key, $front_name);
        }
        return $errors;
    }
}