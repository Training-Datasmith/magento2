<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Account\Edit;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Locale\Option_Interface;
/**
 * Adminhtml edit admin user account form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    public const IDENTITY_VERIFICATION_PASSWORD_FIELD = 'current_password';
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_user_factory;
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_locale_lists;
    /**
     * Operates with deployed locales.
     *
     * @var OptionInterface
     */
    private $deployed_locales;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     * @param OptionInterface $deployedLocales Operates with deployed locales
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\User\Model\User_Factory $user_factory, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Framework\Locale\Lists_Interface $locale_lists, array $data = [], ?Option_Interface $deployed_locales = null)
    {
        $this->_user_factory = $user_factory;
        $this->_auth_session = $auth_session;
        $this->_locale_lists = $locale_lists;
        $this->deployed_locales = $deployed_locales ?: Object_Manager::get_instance()->get(Option_Interface::class);
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * @inheritdoc
     */
    protected function _prepare_form()
    {
        $user_id = $this->_auth_session->get_user()->get_id();
        $user = $this->_user_factory->create()->load($user_id);
        $user->unset_data('password');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create();
        $fieldset = $form->add_fieldset('base_fieldset', ['legend' => __('Account Information')]);
        $fieldset->add_field('username', 'text', ['name' => 'username', 'label' => __('User Name'), 'title' => __('User Name'), 'required' => true]);
        $fieldset->add_field('firstname', 'text', ['name' => 'firstname', 'label' => __('First Name'), 'title' => __('First Name'), 'required' => true]);
        $fieldset->add_field('lastname', 'text', ['name' => 'lastname', 'label' => __('Last Name'), 'title' => __('Last Name'), 'required' => true]);
        $fieldset->add_field('user_id', 'hidden', ['name' => 'user_id']);
        $fieldset->add_field('email', 'text', ['name' => 'email', 'label' => __('Email'), 'title' => __('User Email'), 'required' => true]);
        $fieldset->add_field('password', 'password', ['name' => 'password', 'label' => __('New Password'), 'title' => __('New Password'), 'class' => 'validate-admin-password']);
        $fieldset->add_field('confirmation', 'password', ['name' => 'password_confirmation', 'label' => __('Password Confirmation'), 'class' => 'validate-cpassword']);
        $fieldset->add_field('interface_locale', 'select', ['name' => 'interface_locale', 'label' => __('Interface Locale'), 'title' => __('Interface Locale'), 'values' => $this->deployed_locales->get_translated_option_locales(), 'class' => 'select']);
        $verification_fieldset = $form->add_fieldset('current_user_verification_fieldset', ['legend' => __('Current User Identity Verification')]);
        $verification_fieldset->add_field(self::IDENTITY_VERIFICATION_PASSWORD_FIELD, 'password', ['name' => self::IDENTITY_VERIFICATION_PASSWORD_FIELD, 'label' => __('Your Password'), 'id' => self::IDENTITY_VERIFICATION_PASSWORD_FIELD, 'title' => __('Your Password'), 'class' => 'validate-current-password required-entry', 'required' => true]);
        $data = $user->get_data();
        unset($data[self::IDENTITY_VERIFICATION_PASSWORD_FIELD]);
        $form->set_values($data);
        $form->set_action($this->get_url('adminhtml/system_account/save'));
        $form->set_method('post');
        $form->set_use_container(true);
        $form->set_id('edit_form');
        $this->set_form($form);
        return parent::_prepare_form();
    }
}