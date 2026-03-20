<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message\Media;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Synchronization implements \Magento\Framework\Notification\Message_Interface
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Flag
     */
    protected $_sync_flag;
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity;
    /**
     * Is displayed flag
     *
     * @var bool
     */
    protected $_is_displayed;
    public function __construct(\Magento\Media_Storage\Model\File\Storage\Flag $file_storage)
    {
        $this->_sync_flag = $file_storage->load_self();
    }
    /**
     * Check if message should be displayed
     *
     * @return bool
     */
    abstract protected function _should_be_displayed();
    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function get_identity()
    {
        return $this->_identity;
    }
    /**
     * Check whether
     *
     * @return bool
     */
    public function is_displayed()
    {
        if (null === $this->_is_displayed) {
            $output = $this->_should_be_displayed();
            if ($output) {
                $this->_sync_flag->set_state(\Magento\Media_Storage\Model\File\Storage\Flag::STATE_NOTIFIED);
                $this->_sync_flag->save();
            }
            $this->_is_displayed = $output;
        }
        return $this->_is_displayed;
    }
    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function get_severity()
    {
        return \Magento\Framework\Notification\Message_Interface::SEVERITY_MAJOR;
    }
}