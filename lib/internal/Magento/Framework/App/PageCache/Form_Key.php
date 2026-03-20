<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Page_Cache;

use Magento\Framework\Session\Session_Manager_Interface;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory;
use Magento\Framework\Stdlib\Cookie\Public_Cookie_Metadata;
use Magento\Framework\Stdlib\Cookie_Manager_Interface;
/**
 * Class Version
 *
 */
class Form_Key
{
    /**
     * Name of cookie that holds private content version
     */
    public const COOKIE_NAME = 'form_key';
    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookie_manager;
    /**
     * @var CookieMetadataFactory
     */
    private $cookie_metadata_factory;
    /**
     * @var SessionManagerInterface
     */
    private $session_manager;
    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(Cookie_Manager_Interface $cookie_manager, Cookie_Metadata_Factory $cookie_metadata_factory, Session_Manager_Interface $session_manager)
    {
        $this->cookie_manager = $cookie_manager;
        $this->cookie_metadata_factory = $cookie_metadata_factory;
        $this->session_manager = $session_manager;
    }
    /**
     * Get form key cookie
     *
     * @return string
     */
    public function get()
    {
        return $this->cookie_manager->get_cookie(self::COOKIE_NAME);
    }
    /**
     * @param string $value
     * @param PublicCookieMetadata $metadata
     * @return void
     */
    public function set($value, Public_Cookie_Metadata $metadata)
    {
        $this->cookie_manager->set_public_cookie(self::COOKIE_NAME, $value, $metadata);
    }
    /**
     * @return void
     */
    public function delete()
    {
        $this->cookie_manager->delete_cookie(self::COOKIE_NAME, $this->cookie_metadata_factory->create_cookie_metadata()->set_path($this->session_manager->get_cookie_path())->set_domain($this->session_manager->get_cookie_domain()));
    }
}