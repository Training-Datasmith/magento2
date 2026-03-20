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
 * Create backup controller
 */
class Create extends \Magento\Backup\Controller\Adminhtml\Index implements Http_Post_Action_Interface
{
    /**
     * Create backup action.
     *
     * @return void|\Magento\Backend\App\Action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->is_request_allowed()) {
            return $this->_redirect('*/*/index');
        }
        $response = new \Magento\Framework\Data_Object();
        /**
         * @var \Magento\Backup\Helper\Data $helper
         */
        $helper = $this->_object_manager->get(\Magento\Backup\Helper\Data::class);
        try {
            $type = $this->get_request()->get_param('type');
            if ($type == \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT && $this->get_request()->get_param('exclude_media')) {
                $type = \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA;
            }
            $backup_manager = $this->_backup_factory->create($type)->set_backup_extension($helper->get_extension_by_type($type))->set_time(time())->set_backups_dir($helper->get_backups_dir());
            $backup_manager->set_name($this->get_request()->get_param('backup_name'));
            $this->_core_registry->register('backup_manager', $backup_manager);
            if ($this->get_request()->get_param('maintenance_mode')) {
                $this->maintenance_mode->set(true);
                if (!$this->maintenance_mode->is_on()) {
                    $response->set_error(__('You need more permissions to activate maintenance mode right now.') . ' ' . __('To create the backup, please deselect ' . '"Put store into maintenance mode" or update your permissions.'));
                    $backup_manager->set_error_message(__('Something went wrong while putting your store into maintenance mode.'));
                    return $this->get_response()->represent_json($response->to_json());
                }
            }
            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                /** @var Filesystem $filesystem */
                $filesystem = $this->_object_manager->get(\Magento\Framework\Filesystem::class);
                $backup_manager->set_root_dir($filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path())->add_ignore_paths($helper->get_backup_ignore_paths());
            }
            $success_message = $helper->get_create_success_message_by_type($type);
            $backup_manager->create();
            $this->message_manager->add_success_message($success_message);
            $response->set_redirect_url($this->get_url('*/*/index'));
        } catch (\Magento\Framework\Backup\Exception\Not_Enough_Free_Space $e) {
            $error_message = __('You need more free space to create a backup.');
        } catch (\Magento\Framework\Backup\Exception\Not_Enough_Permissions $e) {
            $this->_object_manager->get(\Psr\Log\Logger_Interface::class)->info($e->get_message());
            $error_message = __('You need more permissions to create a backup.');
        } catch (\Exception $e) {
            $this->_object_manager->get(\Psr\Log\Logger_Interface::class)->info($e->get_message());
            $error_message = __('We can\'t create the backup right now.');
        }
        if (!empty($error_message)) {
            $response->set_error($error_message);
            $backup_manager->set_error_message($error_message);
        }
        if ($this->get_request()->get_param('maintenance_mode')) {
            $this->maintenance_mode->set(false);
        }
        $this->get_response()->represent_json($response->to_json());
    }
    /**
     * Check if request is allowed.
     *
     * @return bool
     */
    private function is_request_allowed()
    {
        return $this->get_request()->is_ajax() && $this->get_request()->is_post();
    }
}