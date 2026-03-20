<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Scope;

use InvalidArgumentException;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Scope\Validator_Interface;
use Magento\Framework\App\Scope_Resolver_Pool;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Phrase;
/**
 * @deprecated 101.0.0 Added in order to avoid backward incompatibility because class was moved to another directory.
 * @see \Magento\Framework\App\Scope\Validator
 */
class Validator implements Validator_Interface
{
    /**
     * @var ScopeResolverPool
     */
    private $scope_resolver_pool;
    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(Scope_Resolver_Pool $scope_resolver_pool)
    {
        $this->scope_resolver_pool = $scope_resolver_pool;
    }
    /**
     * @inheritdoc
     */
    public function is_valid($scope, $scope_code = null)
    {
        if ($scope === Scope_Config_Interface::SCOPE_TYPE_DEFAULT && empty($scope_code)) {
            return true;
        }
        if ($scope === Scope_Config_Interface::SCOPE_TYPE_DEFAULT && !empty($scope_code)) {
            /** @phpstan-ignore-line */
            throw new Localized_Exception(new Phrase('The "%1" scope can\'t include a scope code. Try again without entering a scope code.', [Scope_Config_Interface::SCOPE_TYPE_DEFAULT]));
        }
        if (empty($scope)) {
            throw new Localized_Exception(new Phrase('A scope is missing. Enter a scope and try again.'));
        }
        $this->validate_scope_code($scope_code);
        try {
            $scope_resolver = $this->scope_resolver_pool->get($scope);
            $scope_resolver->get_scope($scope_code)->get_id();
        } catch (InvalidArgumentException $e) {
            throw new Localized_Exception(new Phrase('The "%1" value doesn\'t exist. Enter another value and try again.', [$scope]));
        } catch (No_Such_Entity_Exception $e) {
            throw new Localized_Exception(new Phrase('The "%1" value doesn\'t exist. Enter another value and try again.', [$scope_code]));
        }
        return true;
    }
    /**
     * Validate scope code and throw exception if not valid.
     *
     * @param string $scopeCode
     * @return void
     * @throws LocalizedException if scope code is empty or has a wrong format
     */
    private function validate_scope_code($scope_code)
    {
        if (empty($scope_code)) {
            throw new Localized_Exception(new Phrase('A scope code is missing. Enter a code and try again.'));
        }
        if (!preg_match('/^[a-z]+[a-z0-9_]*$/i', $scope_code)) {
            throw new Localized_Exception(new Phrase('The scope code can include only letters (a-z), numbers (0-9) and underscores (_). ' . 'Also, the first character must be a letter.'));
        }
    }
}