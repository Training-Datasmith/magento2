<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Model;

use Magento\Authorization\Model\User_Context_Interface;
use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Math\Random;
/**
 * Implementation of \Laminas\Captcha\Image
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 *
 * @api
 * @since 100.0.2
 */
class Default_Model extends \Laminas\Captcha\Image implements \Magento\Captcha\Model\Captcha_Interface
{
    /**
     * Key in session for captcha code
     */
    public const SESSION_WORD = 'word';
    /**
     * Min captcha lengths default value
     */
    public const DEFAULT_WORD_LENGTH_FROM = 3;
    /**
     * Max captcha lengths default value
     */
    public const DEFAULT_WORD_LENGTH_TO = 5;
    /**
     * @var Data
     * @since 100.2.0
     */
    protected $captcha_data;
    /**
     * Captcha expire time
     * @var int
     * @since 100.2.0
     */
    protected $expiration;
    /**
     * Override default value to prevent a captcha cut off
     * @var int
     * @see \Laminas\Captcha\Image::$fsize
     * @since 100.2.0
     */
    protected $fsize = 22;
    /**
     * Captcha form id
     * @var string
     * @since 100.2.0
     */
    protected $form_id;
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     * @since 100.2.0
     */
    protected $res_log_factory;
    /**
     * Overrides parent parameter as session comes in constructor.
     *
     * @var bool
     * @since 100.2.0
     */
    protected $keep_session = true;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 100.2.0
     */
    protected $session;
    /**
     * @var string
     */
    private $words;
    /**
     * @var Random
     */
    private $random_math;
    /**
     * @var UserContextInterface
     */
    private $user_context;
    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param ResourceModel\LogFactory $resLogFactory
     * @param string $formId
     * @param Random $randomMath
     * @param UserContextInterface|null $userContext
     * @throws \Laminas\Captcha\Exception\ExtensionNotLoadedException
     */
    public function __construct(\Magento\Framework\Session\Session_Manager_Interface $session, \Magento\Captcha\Helper\Data $captcha_data, \Magento\Captcha\Model\Resource_Model\Log_Factory $res_log_factory, $form_id, ?Random $random_math = null, ?User_Context_Interface $user_context = null)
    {
        parent::__construct();
        $this->session = $session;
        $this->captcha_data = $captcha_data;
        $this->res_log_factory = $res_log_factory;
        $this->form_id = $form_id;
        $this->random_math = $random_math ?? Object_Manager::get_instance()->get(Random::class);
        $this->user_context = $user_context ?? Object_Manager::get_instance()->get(User_Context_Interface::class);
    }
    /**
     * Returns key with respect of current form ID
     *
     * @param string $key
     * @return string
     */
    private function get_form_id_key($key)
    {
        return $this->form_id . '_' . $key;
    }
    /**
     * Get Block Name
     *
     * @return string
     */
    public function get_block_name()
    {
        return \Magento\Captcha\Block\Captcha\Default_Captcha::class;
    }
    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param null|string $login
     * @return bool
     */
    public function is_required($login = null)
    {
        if ($this->is_user_auth() && !$this->is_shown_to_logged_in_user() || !$this->is_enabled() || !in_array($this->form_id, $this->get_target_forms()) || $this->user_context->get_user_type() === User_Context_Interface::USER_TYPE_INTEGRATION) {
            return false;
        }
        return $this->is_show_always() || $this->is_over_limit_attempts($login) || $this->session->get_data($this->get_form_id_key('show_captcha'));
    }
    /**
     * Check if CAPTCHA has to be shown to logged in user on this form
     *
     * @return bool
     */
    public function is_shown_to_logged_in_user()
    {
        $forms = (array) $this->captcha_data->get_config('shown_to_logged_in_user');
        foreach ($forms as $form_id => $is_shown_to_logged_in) {
            if ($is_shown_to_logged_in && $this->form_id == $form_id) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check is over limit attempts
     *
     * @param string $login
     * @return bool
     */
    private function is_over_limit_attempts($login)
    {
        return $this->is_over_limit_ip_attempt() || $this->is_over_limit_login_attempts($login);
    }
    /**
     * Returns number of allowed attempts for same login
     *
     * @return int
     */
    private function get_allowed_attempts_for_same_login()
    {
        return (int) $this->captcha_data->get_config('failed_attempts_login');
    }
    /**
     * Returns number of allowed attempts from same IP
     *
     * @return int
     */
    private function get_allowed_attempts_from_same_ip()
    {
        return (int) $this->captcha_data->get_config('failed_attempts_ip');
    }
    /**
     * Check is over limit saved attempts from one ip
     *
     * @return bool
     */
    private function is_over_limit_ip_attempt()
    {
        $count_attempts_by_ip = $this->get_resource_model()->count_attempts_by_remote_address();
        return $count_attempts_by_ip >= $this->get_allowed_attempts_from_same_ip();
    }
    /**
     * Is Over Limit Login Attempts
     *
     * @param string $login
     * @return bool
     */
    private function is_over_limit_login_attempts($login)
    {
        if ($login != false) {
            $count_attempts_by_login = $this->get_resource_model()->count_attempts_by_user_login($login);
            return $count_attempts_by_login >= $this->get_allowed_attempts_for_same_login();
        }
        return false;
    }
    /**
     * Check is user auth
     *
     * @return bool
     */
    private function is_user_auth()
    {
        return $this->session->is_logged_in() || $this->user_context->get_user_id();
    }
    /**
     * Whether to respect case while checking the answer
     *
     * @return bool
     */
    public function is_case_sensitive()
    {
        return (string) $this->captcha_data->get_config('case_sensitive');
    }
    /**
     * Get font to use when generating captcha
     *
     * @return string
     */
    public function get_font()
    {
        $font = (string) $this->captcha_data->get_config('font');
        $fonts = $this->captcha_data->get_fonts();
        if (isset($fonts[$font])) {
            $font_path = $fonts[$font]['path'];
        } else {
            $font_data = array_shift($fonts);
            $font_path = $font_data['path'];
        }
        return $font_path;
    }
    /**
     * After this time isCorrect() is going to return FALSE even if word was guessed correctly
     *
     * @return int
     */
    public function get_expiration()
    {
        if (!$this->expiration) {
            /**
             * as "timeout" configuration parameter specifies timeout in minutes - we multiply it on 60 to set
             * expiration in seconds
             */
            $this->expiration = (int) $this->captcha_data->get_config('timeout') * 60;
        }
        return $this->expiration;
    }
    /**
     * Get timeout for session token
     *
     * @return int
     */
    public function get_timeout()
    {
        return $this->get_expiration();
    }
    /**
     * Get captcha image directory
     *
     * @return string
     */
    public function get_img_dir()
    {
        return $this->captcha_data->get_img_dir();
    }
    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function get_img_url()
    {
        return $this->captcha_data->get_img_url();
    }
    /**
     * Checks whether captcha was guessed correctly by user
     *
     * @param string $word
     * @return bool
     */
    public function is_correct($word)
    {
        $stored_words = $this->get_words();
        $this->clear_word();
        if (!$word || !$stored_words) {
            return false;
        }
        if (!$this->is_case_sensitive()) {
            $stored_words = strtolower($stored_words);
            $word = strtolower($word);
        }
        return in_array($word, explode(',', $stored_words));
    }
    /**
     * Return full URL to captcha image
     *
     * @return string
     */
    public function get_img_src()
    {
        return $this->get_img_url() . $this->get_id() . $this->get_suffix();
    }
    /**
     * Log attempt
     *
     * @param string $login
     * @return $this
     */
    public function log_attempt($login)
    {
        if ($this->is_enabled() && in_array($this->form_id, $this->get_target_forms())) {
            $this->get_resource_model()->log_attempt($login);
            if ($this->is_over_limit_login_attempts($login)) {
                $this->set_show_captcha_in_session(true);
            }
        }
        return $this;
    }
    /**
     * Set show_captcha flag in session
     *
     * @param bool $value
     * @return void
     * @since 100.1.0
     */
    public function set_show_captcha_in_session($value = true)
    {
        if ($value !== true) {
            $value = false;
        }
        $this->session->set_data($this->get_form_id_key('show_captcha'), $value);
    }
    /**
     * Generate word used for captcha render
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    protected function generate_word()
    {
        $symbols = (string) $this->captcha_data->get_config('symbols');
        $word_len = $this->get_word_len();
        return $this->random_math->get_random_string($word_len, $symbols);
    }
    /**
     * Returns length for generating captcha word. This value may be dynamic.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    public function get_word_len()
    {
        $from = 0;
        $to = 0;
        $length = (string) $this->captcha_data->get_config('length');
        if (!is_numeric($length)) {
            if (preg_match('/(\d+)-(\d+)/', $length, $matches)) {
                $from = (int) $matches[1];
                $to = (int) $matches[2];
            }
        } else {
            $from = (int) $length;
            $to = (int) $length;
        }
        if ($to < $from || $from < 1 || $to < 1) {
            $from = self::DEFAULT_WORD_LENGTH_FROM;
            $to = self::DEFAULT_WORD_LENGTH_TO;
        }
        return Random::get_random_number($from, $to);
    }
    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    private function is_show_always()
    {
        $captcha_mode = (string) $this->captcha_data->get_config('mode');
        if ($captcha_mode === Data::MODE_ALWAYS) {
            return true;
        }
        if ($captcha_mode === Data::MODE_AFTER_FAIL && $this->get_allowed_attempts_for_same_login() === 0) {
            return true;
        }
        $always_for = $this->captcha_data->get_config('always_for');
        foreach ($always_for as $node_form_id => $is_always_for) {
            if ($is_always_for && $this->form_id == $node_form_id) {
                return true;
            }
        }
        return false;
    }
    /**
     * Whether captcha is enabled at this area
     *
     * @return bool
     */
    private function is_enabled()
    {
        return (string) $this->captcha_data->get_config('enable');
    }
    /**
     * Retrieve list of forms where captcha must be shown
     *
     * For frontend this list is based on current website
     *
     * @return array
     */
    private function get_target_forms()
    {
        $forms_string = (string) $this->captcha_data->get_config('forms');
        return explode(',', $forms_string);
    }
    /**
     * Get captcha word
     *
     * @return string|null
     */
    public function get_word()
    {
        $session_data = $this->session->get_data($this->get_form_id_key(self::SESSION_WORD));
        return time() < $session_data['expires'] ? $session_data['data'] : null;
    }
    /**
     * Get captcha words
     *
     * @return string
     */
    private function get_words()
    {
        $session_data = $this->session->get_data($this->get_form_id_key(self::SESSION_WORD));
        $words = '';
        if (isset($session_data['expires'], $session_data['words']) && time() < $session_data['expires']) {
            $words = $session_data['words'];
        }
        return $words;
    }
    /**
     * Set captcha word
     *
     * @param string $word
     * @return $this
     * @since 100.2.0
     */
    protected function set_word($word)
    {
        $this->words = $this->words ? $this->words . ',' . $word : $word;
        $this->session->set_data($this->get_form_id_key(self::SESSION_WORD), ['data' => $word, 'words' => $this->words, 'expires' => time() + $this->get_timeout()]);
        $this->word = $word;
        return $this;
    }
    /**
     * Set captcha word
     *
     * @return $this
     */
    private function clear_word()
    {
        $this->session->unset_data($this->get_form_id_key(self::SESSION_WORD));
        $this->word = null;
        return $this;
    }
    /**
     * Override function to generate less curly captcha that will not cut off
     *
     * @see \Laminas\Captcha\Image::_randomSize()
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    protected function random_size()
    {
        return Random::get_random_number(280, 300) / 100;
    }
    /**
     * Overlap of the parent method
     *
     * @return void
     *
     * Now deleting old captcha images make crontab script
     * @see \Magento\Captcha\Cron\DeleteExpiredImages::execute
     *
     * Added SuppressWarnings since this method is declared in parent class and we can not use other method name.
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 100.2.0
     */
    protected function gc()
    {
        // phpcs:ignore
        return;
        // required for static testing to pass
    }
    /**
     * Get resource model
     *
     * @return \Magento\Captcha\Model\ResourceModel\Log
     */
    private function get_resource_model()
    {
        return $this->res_log_factory->create();
    }
}