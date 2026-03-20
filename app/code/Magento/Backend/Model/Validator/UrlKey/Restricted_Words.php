<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Model\Validator\Url_Key;

use Magento\Framework\Validator\Url_Key;
/**
 * Class RestrictedWords validates if urlKey doesn't matches restricted words(endpoint names)
 */
class Restricted_Words implements Url_Key_Validator_Interface
{
    /**
     * @var UrlKey
     */
    private $url_key;
    /**
     * @param UrlKey $urlKey
     */
    public function __construct(Url_Key $url_key)
    {
        $this->url_key = $url_key;
    }
    /**
     * @inheritDoc
     */
    public function validate(string $url_key): array
    {
        $errors = [];
        if (!$this->url_key->is_valid($url_key)) {
            $errors[] = __('URL key "%1" matches a reserved endpoint name (%2). Use another URL key.', $url_key, implode(', ', $this->url_key->get_restricted_values()));
        }
        return $errors;
    }
}