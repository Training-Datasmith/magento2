<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem;
/**
 * Backup rollback controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rollback extends \Magento\Backup\Controller\Adminhtml\Index implements Http_Post_Action_Interface
{
    /**
     * Rollback Action
     *
     * @return void|\Magento\Backend\App\Action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_object_manager->get(\Magento\Backup\Helper\Data::class)->is_rollback_allowed()) {
            $this->_forward('denied');
        }
        if (!$this->get_request()->is_ajax()) {
            return $this->_redirect('*/*/index');
        }
        $helper = $this->_object_manager->get(\Magento\Backup\Helper\Data::class);
        $response = new \Magento\Framework\Data_Object();
        try {
            /* @var $backup \Magento\Backup\Model\Backup */
            $backup = $this->_backup_model_factory->create($this->get_request()->get_param('time'), $this->get_request()->get_param('type'));
            if (!$backup->get_time() || !$backup->exists()) {
                return $this->_redirect('backup/*');
            }
            if (!$backup->get_time()) {
                throw new \Magento\Framework\Backup\Exception\Cant_Load_Snapshot(__('Can\'t load snapshot archive'));
            }
            $type = $backup->get_type();
            $backup_manager = $this->_backup_factory->create($type)->set_backup_extension($helper->get_extension_by_type($type))->set_time($backup->get_time())->set_backups_dir($helper->get_backups_dir())->set_name($backup->get_name(), false)->set_resource_model($this->_object_manager->create(\Magento\Backup\Model\Resource_Model\Db::class));
            $this->_core_registry->register('backup_manager', $backup_manager);
            $password_valid = $this->_object_manager->create(\Magento\Backup\Model\Backup::class)->validate_user_password($this->get_request()->get_param('password'));
            if (!$password_valid) {
                $response->set_error(__('Please correct the password.'));
                $backup_manager->set_error_message(__('Please correct the password.'));
                return $this->get_response()->represent_json($response->to_json());
            }
            if ($this->get_request()->get_param('maintenance_mode')) {
                $this->maintenance_mode->set(true);
                if (!$this->maintenance_mode->is_on()) {
                    $response->set_error(__('You need more permissions to activate maintenance mode right now.') . ' ' . __('To complete the rollback, please deselect ' . '"Put store into maintenance mode" or update your permissions.'));
                    $backup_manager->set_error_message(__('Something went wrong while putting your store into maintenance mode.'));
                    return $this->get_response()->represent_json($response->to_json());
                }
            }
            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                /** @var Filesystem $filesystem */
                $filesystem = $this->_object_manager->get(\Magento\Framework\Filesystem::class);
                $backup_manager->set_root_dir($filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path())->add_ignore_paths($helper->get_rollback_ignore_paths());
                if ($this->get_request()->get_param('use_ftp', false)) {
                    $backup_manager->set_use_ftp($this->get_request()->get_param('ftp_host', ''), $this->get_request()->get_param('ftp_user', ''), $this->get_request()->get_param('ftp_pass', ''), $this->get_request()->get_param('ftp_path', ''));
                }
            }
            $backup_manager->rollback();
            $helper->invalidate_cache();
            $admin_session = $this->_get_session();
            $admin_session->destroy();
            $response->set_redirect_url($this->get_url('*'));
        } catch (\Magento\Framework\Backup\Exception\Cant_Load_Snapshot $e) {
            $error_msg = __('We can\'t find the backup file.');
        } catch (\Magento\Framework\Backup\Exception\Ftp_Connection_Failed $e) {
            $error_msg = __('We can\'t connect to the FTP right now.');
        } catch (\Magento\Framework\Backup\Exception\Ftp_Validation_Failed $e) {
            $error_msg = __('Failed to validate FTP.');
        } catch (\Magento\Framework\Backup\Exception\Not_Enough_Permissions $e) {
            $this->_object_manager->get(\Psr\Log\Logger_Interface::class)->info($e->get_message());
            $error_msg = __('You need more permissions to perform a rollback.');
        } catch (\Exception $e) {
            $this->_object_manager->get(\Psr\Log\Logger_Interface::class)->info($e->get_message());
            $error_msg = __('Failed to rollback.');
        }
        if (!empty($error_msg)) {
            $response->set_error($error_msg);
            $backup_manager->set_error_message($error_msg);
        }
        if ($this->get_request()->get_param('maintenance_mode')) {
            $this->maintenance_mode->set(false);
        }
        $this->get_response()->represent_json($response->to_json());
    }
}