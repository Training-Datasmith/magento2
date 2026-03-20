<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Framework\Cache\Lock_Guarded_Cache_Loader;
/**
 * Constructor modification point for Magento\Backend\Block\Widget.
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
class Context extends \Magento\Backend\Block\Template\Context
{
    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $button_list;
    /**
     * @var \Magento\Backend\Block\Widget\Button\ToolbarInterface
     */
    protected $button_toolbar;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $page_config;
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Session\Generic $session
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
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\View\TemplateEnginePool $enginePool
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Element\Template\File\Resolver $resolver
     * @param \Magento\Framework\View\Element\Template\File\Validator $validator
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @param Button\ToolbarInterface $toolbar
     * @param LockGuardedCacheLoader|null $lockQuery
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request_Interface $request, \Magento\Framework\View\Layout_Interface $layout, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Url_Interface $url_builder, \Magento\Framework\App\Cache_Interface $cache, \Magento\Framework\View\Design_Interface $design, \Magento\Framework\Session\Generic $session, \Magento\Framework\Session\Sid_Resolver_Interface $sid_resolver, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\View\Asset\Repository $asset_repo, \Magento\Framework\View\Config_Interface $view_config, \Magento\Framework\App\Cache\State_Interface $cache_state, \Psr\Log\Logger_Interface $logger, \Magento\Framework\Escaper $escaper, \Magento\Framework\Filter\Filter_Manager $filter_manager, \Magento\Framework\Stdlib\DateTime\Timezone_Interface $locale_date, \Magento\Framework\Translate\Inline\State_Interface $inline_translation, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\View\File_System $view_file_system, \Magento\Framework\View\Template_Engine_Pool $engine_pool, \Magento\Framework\App\State $app_state, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Framework\View\Page\Config $page_config, \Magento\Framework\View\Element\Template\File\Resolver $resolver, \Magento\Framework\View\Element\Template\File\Validator $validator, \Magento\Framework\Authorization_Interface $authorization, \Magento\Backend\Model\Session $backend_session, \Magento\Framework\Math\Random $math_random, \Magento\Framework\Data\Form\Form_Key $form_key, \Magento\Framework\Code\Name_Builder $name_builder, Button\Button_List $button_list, Button\Toolbar_Interface $toolbar, ?Lock_Guarded_Cache_Loader $lock_query = null)
    {
        parent::__construct($request, $layout, $event_manager, $url_builder, $cache, $design, $session, $sid_resolver, $scope_config, $asset_repo, $view_config, $cache_state, $logger, $escaper, $filter_manager, $locale_date, $inline_translation, $filesystem, $view_file_system, $engine_pool, $app_state, $store_manager, $page_config, $resolver, $validator, $authorization, $backend_session, $math_random, $form_key, $name_builder, $lock_query);
        $this->button_list = $button_list;
        $this->button_toolbar = $toolbar;
    }
    /**
     * Get button list
     *
     * @return \Magento\Backend\Block\Widget\Button\ButtonList
     */
    public function get_button_list()
    {
        return $this->button_list;
    }
    /**
     * Get button toolbar
     *
     * @return \Magento\Backend\Block\Widget\Button\ToolbarInterface
     */
    public function get_button_toolbar()
    {
        return $this->button_toolbar;
    }
}