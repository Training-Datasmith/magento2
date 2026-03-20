<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data\Form;

/**
 * Class FormKey
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Form_Key
{
    /**
     * Form key
     */
    public const FORM_KEY = '_form_key';
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $math_random;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;
    /**
     * @var \Magento\Framework\Escaper
     * @since 100.0.3
     */
    protected $escaper;
    /**
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(\Magento\Framework\Math\Random $math_random, \Magento\Framework\Session\Session_Manager_Interface $session, \Magento\Framework\Escaper $escaper)
    {
        $this->math_random = $math_random;
        $this->session = $session;
        $this->escaper = $escaper;
    }
    /**
     * Retrieve Session Form Key
     *
     * @return string A 16 bit unique key for forms
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get_form_key()
    {
        if (!$this->is_present()) {
            $this->set($this->math_random->get_random_string(16));
        }
        return $this->escaper->escape_js($this->session->get_data(self::FORM_KEY));
    }
    /**
     * Determine if the form key is present in the session
     *
     * @return bool
     */
    public function is_present()
    {
        return (bool) $this->session->get_data(self::FORM_KEY);
    }
    /**
     * Set the value of the form key
     *
     * @param string $value
     * @return void
     */
    public function set($value)
    {
        $this->session->set_data(self::FORM_KEY, $value);
    }
}