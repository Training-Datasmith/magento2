<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

use Magento\Framework\Cache\Lock_Guarded_Cache_Loader;
/**
 * Constructor modification point for Magento\Backend\Block\AbstractBlock.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @api
 * @SuppressWarnings(PHPMD)
 * @since 100.0.2
 */
class Context extends \Magento\Framework\View\Element\Context
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param LockGuardedCacheLoader|null $lockQuery
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request_Interface $request, \Magento\Framework\View\Layout_Interface $layout, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Url_Interface $url_builder, \Magento\Framework\App\Cache_Interface $cache, \Magento\Framework\View\Design_Interface $design, \Magento\Framework\Session\Session_Manager_Interface $session, \Magento\Framework\Session\Sid_Resolver_Interface $sid_resolver, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\View\Asset\Repository $asset_repo, \Magento\Framework\View\Config_Interface $view_config, \Magento\Framework\App\Cache\State_Interface $cache_state, \Psr\Log\Logger_Interface $logger, \Magento\Framework\Escaper $escaper, \Magento\Framework\Filter\Filter_Manager $filter_manager, \Magento\Framework\Stdlib\DateTime\Timezone_Interface $locale_date, \Magento\Framework\Translate\Inline\State_Interface $inline_translation, \Magento\Framework\Authorization_Interface $authorization, ?Lock_Guarded_Cache_Loader $lock_query = null)
    {
        $this->_authorization = $authorization;
        parent::__construct($request, $layout, $event_manager, $url_builder, $cache, $design, $session, $sid_resolver, $scope_config, $asset_repo, $view_config, $cache_state, $logger, $escaper, $filter_manager, $locale_date, $inline_translation, $lock_query);
    }
    /**
     * Retrieve Authorization
     *
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
}