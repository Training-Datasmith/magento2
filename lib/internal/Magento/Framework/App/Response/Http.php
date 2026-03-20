<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Session\Config\Config_Interface;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory;
use Magento\Framework\Stdlib\Cookie_Manager_Interface;
use Magento\Framework\Stdlib\DateTime;
/**
 * HTTP Response.
 *
 * @api
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
#[\Allow_Dynamic_Properties]
class Http extends \Magento\Framework\HTTP\Php_Environment\Response
{
    /** Cookie to store page vary string */
    public const COOKIE_VARY_STRING = 'X-Magento-Vary';
    /** Format for expiration timestamp headers */
    public const EXPIRATION_TIMESTAMP_FORMAT = 'D, d M Y H:i:s T';
    /** X-FRAME-OPTIONS Header name */
    public const HEADER_X_FRAME_OPT = 'X-Frame-Options';
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookie_manager;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookie_metadata_factory;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $date_time;
    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    private $session_config;
    /**
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param ConfigInterface|null $sessionConfig
     */
    public function __construct(Http_Request $request, Cookie_Manager_Interface $cookie_manager, Cookie_Metadata_Factory $cookie_metadata_factory, Context $context, DateTime $date_time, ?Config_Interface $session_config = null)
    {
        $this->request = $request;
        $this->cookie_manager = $cookie_manager;
        $this->cookie_metadata_factory = $cookie_metadata_factory;
        $this->context = $context;
        $this->date_time = $date_time;
        $this->session_config = $session_config ?: Object_Manager::get_instance()->get(Config_Interface::class);
    }
    /**
     * Sends the X-FRAME-OPTIONS header to protect against click-jacking
     *
     * @param string $value
     * @return void
     */
    public function set_x_frame_options($value)
    {
        $this->set_header(self::HEADER_X_FRAME_OPT, $value);
    }
    /**
     * Send Vary cookie
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function send_vary()
    {
        $vary_string = $this->context->get_vary_string();
        if ($vary_string) {
            $cookie_life_time = $this->session_config->get_cookie_lifetime();
            $sensitive_cook_metadata = $this->cookie_metadata_factory->create_sensitive_cookie_metadata([Cookie_Metadata::KEY_DURATION => $cookie_life_time, Cookie_Metadata::KEY_SAME_SITE => 'Lax'])->set_path('/');
            $this->cookie_manager->set_sensitive_cookie(self::COOKIE_VARY_STRING, $vary_string, $sensitive_cook_metadata);
        } elseif ($this->request->get(self::COOKIE_VARY_STRING)) {
            $cookie_metadata = $this->cookie_metadata_factory->create_sensitive_cookie_metadata()->set_path('/');
            $this->cookie_manager->delete_cookie(self::COOKIE_VARY_STRING, $cookie_metadata);
        }
    }
    /**
     * Set headers for public cache
     *
     * Accepts the time-to-live (max-age) parameter
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function set_public_headers($ttl)
    {
        if ($ttl === null || $ttl < 0 || !preg_match('/^[0-9]+$/', $ttl)) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set public headers');
        }
        $this->set_header('pragma', 'cache', true);
        $this->set_header('cache-control', 'public, max-age=' . $ttl . ', s-maxage=' . $ttl, true);
        $this->set_header('expires', $this->get_expiration_header('+' . $ttl . ' seconds'), true);
    }
    /**
     * Set headers for private cache
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function set_private_headers($ttl)
    {
        if (!$ttl) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set private headers');
        }
        $this->set_header('pragma', 'cache', true);
        $this->set_header('cache-control', 'private, max-age=' . $ttl, true);
        $this->set_header('expires', $this->get_expiration_header('+' . $ttl . ' seconds'), true);
    }
    /**
     * Set headers for no-cache responses
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function set_no_cache_headers()
    {
        $this->set_header('pragma', 'no-cache', true);
        $this->set_header('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->set_header('expires', $this->get_expiration_header('-1 year'), true);
    }
    /**
     * Represents an HTTP response body in JSON format by sending appropriate header
     *
     * @param string $content String in JSON format
     * @return \Magento\Framework\App\Response\Http
     * @codeCoverageIgnore
     */
    public function represent_json($content)
    {
        $this->set_header('Content-Type', 'application/json', true);
        return $this->set_content($content);
    }
    /**
     * Remove links to other objects.
     *
     * @return string[]
     * @codeCoverageIgnore
     */
    public function __sleep()
    {
        return ['content', 'isRedirect', 'statusCode', 'context', 'headers'];
    }
    /**
     * Need to reconstruct dependencies when being de-serialized.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function __wakeup()
    {
        $object_manager = Object_Manager::get_instance();
        $this->cookie_manager = $object_manager->create(\Magento\Framework\Stdlib\Cookie_Manager_Interface::class);
        $this->cookie_metadata_factory = $object_manager->get(\Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory::class);
        $this->request = $object_manager->get(\Magento\Framework\App\Request\Http::class);
    }
    /**
     * Given a time input, returns the formatted header
     *
     * @param string $time
     * @return string
     * @codeCoverageIgnore
     */
    protected function get_expiration_header($time)
    {
        return $this->date_time->gm_date(self::EXPIRATION_TIMESTAMP_FORMAT, $this->date_time->str_to_time($time));
    }
}