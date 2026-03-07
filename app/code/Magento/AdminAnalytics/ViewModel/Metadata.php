<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AdminAnalytics\ViewModel;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Information;

/**
 * Gets user version and mode
 */
class Metadata implements ArgumentInterface
{
    /**
     * @var string
     */
    private $nonce;

    /**
     * @var CspNonceProvider
     */
    private $nonceProvider;

    public function __construct(
        private readonly ProductMetadataInterface $productMetadata,
        private readonly Session $authSession,
        private readonly State $appState,
        private readonly ScopeConfigInterface $config,
        ?CspNonceProvider $nonceProvider = null
    ) {
        $this->nonceProvider = $nonceProvider ?: ObjectManager::getInstance()->get(CspNonceProvider::class);

        $this->nonce = $this->nonceProvider->generateNonce();
    }

    /**
     * Get product version
     */
    public function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get product edition
     */
    public function getProductEdition(): string
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Get current user id (hash generated from email)
     */
    public function getCurrentUser(): string
    {
        return hash('sha256', 'ADMIN_USER' . $this->authSession->getUser()->getEmail());
    }

    /**
     * Get Magento mode that the user is using
     */
    public function getMode(): string
    {
        return $this->appState->getMode();
    }

    /**
     * Get created date for current user
     */
    public function getCurrentUserCreatedDate(): string
    {
        return $this->authSession->getUser()->getCreated();
    }

    /**
     * Get log date for current user
     */
    public function getCurrentUserLogDate(): ?string
    {
        return $this->authSession->getUser()->getLogdate();
    }

    /**
     * Get secure base URL
     */
    public function getSecureBaseUrlForScope(
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): ?string {
        return $this->config->getValue(Custom::XML_PATH_SECURE_BASE_URL, $scope, $scopeCode);
    }

    /**
     * Get store name
     */
    public function getStoreNameForScope(
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): ?string {
        return $this->config->getValue(Information::XML_PATH_STORE_INFO_NAME, $scope, $scopeCode);
    }

    /**
     * Get current user role name
     */
    public function getCurrentUserRoleName(): string
    {
        return $this->authSession->getUser()->getRole()->getRoleName();
    }

    /**
     * Get a random nonce for each request.
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }
}
