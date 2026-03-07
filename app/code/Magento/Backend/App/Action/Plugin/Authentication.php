<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\App\Action\Plugin;

use Magento\Framework\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authentication
{
    /**
     * @var string[]
     */
    protected $_openActions = [
        'forgotpassword',
        'resetpassword',
        'resetpasswordpost',
        'logout',
        'refresh', // captcha refresh
    ];

    public function __construct(protected \Magento\Backend\Model\Auth $_auth, protected \Magento\Backend\Model\UrlInterface $_url, protected \Magento\Framework\App\ResponseInterface $_response, protected \Magento\Framework\App\ActionFlag $_actionFlag, protected \Magento\Framework\Message\ManagerInterface $messageManager, protected \Magento\Backend\Model\UrlInterface $backendUrl, protected \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory, protected \Magento\Backend\App\BackendAppList $backendAppList, protected \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator)
    {
    }

    /**
     * Ensures user is authenticated before accessing backend action controllers.
     *
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Backend\App\AbstractAction $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $requestedActionName = $request->getActionName();
        if (in_array($requestedActionName, $this->_openActions)) {
            $request->setDispatched(true);
        } else {
            if ($this->_auth->getUser()) {
                $this->_auth->getUser()->reload();
            }
            if (!$this->_auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            } else {
                $this->_auth->getAuthStorage()->prolong();

                $backendApp = null;
                if ($request->getParam('app')) {
                    $backendApp = $this->backendAppList->getCurrentApp();
                }

                if ($backendApp) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($this->backendUrl->getBaseUrl());
                    $baseUrl = $baseUrl . $backendApp->getStartupPage();
                    return $resultRedirect->setUrl($baseUrl);
                }
            }
        }
        $this->_auth->getAuthStorage()->refreshAcl();
        return $proceed($request);
    }

    /**
     * Process not logged in user data
     *
     * @return void
     */
    protected function _processNotLoggedInUser(\Magento\Framework\App\RequestInterface $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login')) {
            if ($this->formKeyValidator->validate($request)) {
                if ($this->_performLogin($request)) {
                    $isRedirectNeeded = $this->_redirectIfNeededAfterLogin($request);
                }
            } else {
                $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                $this->_response->setRedirect($this->_url->getCurrentUrl());
                $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));
                $isRedirectNeeded = true;
            }
        }
        if (!$isRedirectNeeded && !$request->isForwarded()) {
            if ($request->getParam('isIframe')) {
                $request->setForwarded(true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedIframe')
                    ->setDispatched(false);
            } elseif ($request->getParam('isAjax')) {
                $request->setForwarded(true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedJson')
                    ->setDispatched(false);
            } else {
                $request->setForwarded(true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('login')
                    ->setDispatched(false);
            }
        }
    }

    /**
     * Performs login, if user submitted login form
     *
     * @return bool
     */
    protected function _performLogin(\Magento\Framework\App\RequestInterface $request)
    {
        $outputValue = true;
        $postLogin = $request->getPost('login');
        $username = $postLogin['username'] ?? '';
        $password = $postLogin['password'] ?? '';
        $request->setPostValue('login', null);

        try {
            $this->_auth->login($username, $password);
        } catch (AuthenticationException $e) {
            if (!$request->getParam('messageSent')) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $request->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     */
    protected function _redirectIfNeededAfterLogin(\Magento\Framework\App\RequestInterface $request): bool
    {
        $requestUri = null;

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_url->useSecretKey()) {
            // The requested URL has an invalid secret key and therefore redirecting to this URL
            // will cause a security vulnerability.
            $requestUri = $this->_url->getUrl($this->_url->getStartupPageUrl());
        } elseif ($request) {
            $requestUri = $request->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->_response->setRedirect($requestUri);
        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
        return true;
    }
}
