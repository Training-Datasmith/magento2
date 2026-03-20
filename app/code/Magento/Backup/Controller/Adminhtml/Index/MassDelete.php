<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class Mass_Delete extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Delete backups mass action
     *
     * @return \Magento\Backend\App\Action
     */
    public function execute()
    {
        $backup_ids = $this->get_request()->get_param('ids', []);
        if (!is_array($backup_ids) || !count($backup_ids)) {
            return $this->_redirect('backup/*/index');
        }
        $result_data = new \Magento\Framework\Data_Object();
        $result_data->set_is_success(false);
        $result_data->set_delete_result([]);
        $this->_core_registry->register('backup_manager', $result_data);
        $delete_fail_message = __('We can\'t delete one or more backups.');
        try {
            $all_backups_deleted = true;
            foreach ($backup_ids as $id) {
                list($time, $type) = explode('_', $id);
                $backup_model = $this->_backup_model_factory->create($time, $type)->delete_file();
                if ($backup_model->exists()) {
                    $all_backups_deleted = false;
                    $result = __('failed');
                } else {
                    $result = __('successful');
                }
                $result_data->set_delete_result(array_merge($result_data->get_delete_result(), [$backup_model->get_file_name() . ' ' . $result]));
            }
            $result_data->set_is_success(true);
            if ($all_backups_deleted) {
                $this->message_manager->add_success_message(__('You deleted the selected backup(s).'));
            } else {
                throw new \Exception($delete_fail_message);
            }
        } catch (\Exception $e) {
            $result_data->set_is_success(false);
            $this->message_manager->add_error_message($delete_fail_message);
        }
        return $this->_redirect('backup/*/index');
    }
}