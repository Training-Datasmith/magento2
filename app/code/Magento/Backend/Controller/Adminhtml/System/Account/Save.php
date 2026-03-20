<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\State\User_Locked_Exception;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Security\Model\Security_Cookie;
use Magento\User\Model\User;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Account
{
    /**
     * @var SecurityCookie
     */
    private $security_cookie;
    /**
     * Get security cookie
     *
     * @deprecated 100.1.0 This method is deprecated because dependency injection should be used instead of
     *                     directly accessing the SecurityCookie instance.
     *                     Use dependency injection to get an instance of SecurityCookie.
     * @see \Magento\Backend\Controller\Adminhtml\System\Account::__construct()
     * @return SecurityCookie
     */
    private function get_security_cookie()
    {
        if (!$this->security_cookie instanceof Security_Cookie) {
            return \Magento\Framework\App\Object_Manager::get_instance()->get(Security_Cookie::class);
        }
        return $this->security_cookie;
    }
    /**
     * Saving edited user information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $user_id = $this->_object_manager->get(\Magento\Backend\Model\Auth\Session::class)->get_user()->get_id();
        $password = (string) $this->get_request()->get_param('password');
        $password_confirmation = (string) $this->get_request()->get_param('password_confirmation');
        $interface_locale = (string) $this->get_request()->get_param('interface_locale', false);
        /** @var $user \Magento\User\Model\User */
        $user = $this->_object_manager->create(\Magento\User\Model\User::class)->load($user_id);
        $user->set_id($user_id)->set_user_name($this->get_request()->get_param('username', false))->set_first_name($this->get_request()->get_param('firstname', false))->set_last_name($this->get_request()->get_param('lastname', false))->set_email(strtolower($this->get_request()->get_param('email', false)));
        if ($this->_object_manager->get(\Magento\Framework\Validator\Locale::class)->is_valid($interface_locale)) {
            $user->set_interface_locale($interface_locale);
            /** @var \Magento\Backend\Model\Locale\Manager $localeManager */
            $locale_manager = $this->_object_manager->get(\Magento\Backend\Model\Locale\Manager::class);
            $locale_manager->switch_backend_interface_locale($interface_locale);
        }
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $current_user_password_field = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $current_user_password = $this->get_request()->get_param($current_user_password_field);
        try {
            $user->perform_identity_check($current_user_password);
            if ($password !== '') {
                $user->set_password($password);
                $user->set_password_confirmation($password_confirmation);
            }
            $errors = $user->validate();
            if ($errors !== true && !empty($errors)) {
                foreach ($errors as $error) {
                    $this->message_manager->add_error_message($error);
                }
            } else {
                $user->save();
                $user->send_notification_emails_if_required();
                $modified_fields = $this->get_modified_fields($user);
                if (!empty($modified_fields)) {
                    $count_modified_fields = count($modified_fields);
                    // validate how many fields were modified to display them correctly
                    if ($count_modified_fields > 1) {
                        $last_modified_field = array_pop($modified_fields);
                        $modified_fields_text = implode(', ', $modified_fields);
                        $success_message = __('The %1 and %2 of this account have been modified successfully.', $modified_fields_text, $last_modified_field);
                    } else {
                        $success_message = __('The %1 of this account has been modified successfully.', reset($modified_fields));
                    }
                    $this->message_manager->add_success_message($success_message);
                } else {
                    $this->message_manager->add_success_message(__('You saved the account.'));
                }
            }
        } catch (User_Locked_Exception $e) {
            $this->_auth->logout();
            $this->get_security_cookie()->set_logout_reason_cookie(\Magento\Security\Model\Admin_Sessions_Manager::LOGOUT_REASON_USER_LOCKED);
        } catch (Validator_Exception $e) {
            $this->message_manager->add_messages($e->get_messages());
            if ($e->get_message()) {
                $this->message_manager->add_error_message($e->get_message());
            }
        } catch (Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_error_message(__('An error occurred while saving account.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        return $result_redirect->set_path('*/*/');
    }
    /**
     * Get user modified fields
     *
     * @param User $user
     * @return array
     */
    private function get_modified_fields(User $user)
    {
        $modified_fields = [];
        $properties_to_check = ['password', 'username', 'firstname', 'lastname', 'email'];
        foreach ($properties_to_check as $property) {
            if ($user->get_orig_data($property) !== $user->{'get' . ucfirst($property)}()) {
                $modified_fields[] = $property;
            }
        }
        return $modified_fields;
    }
}