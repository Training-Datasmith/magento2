<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Model\Resource_Model\View;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Backup\Db\Backup_Interface;
use Magento\Framework\DB\Adapter\Adapter_Interface;
/**
 * Creates backup of Views in the database.
 */
class Create_Views_Backup
{
    /**
     * @var GetListViews
     */
    private $get_list_views;
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * @var AdapterInterface
     */
    private $connection;
    /**
     * @param GetListViews $getListViews
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(Get_List_Views $get_list_views, Resource_Connection $resource_connection)
    {
        $this->get_list_views = $get_list_views;
        $this->resource_connection = $resource_connection;
    }
    /**
     * Write backup data to backup file.
     *
     * @param BackupInterface $backup
     */
    public function execute(Backup_Interface $backup): void
    {
        $views = $this->get_list_views->execute();
        foreach ($views as $view) {
            $backup->write($this->get_view_header($view));
            $backup->write($this->get_drop_view_sql($view));
            $backup->write($this->get_create_view($view));
        }
    }
    /**
     * Retrieve Database connection for Backup.
     *
     * @return AdapterInterface
     */
    private function get_connection(): Adapter_Interface
    {
        if (!$this->connection) {
            $this->connection = $this->resource_connection->get_connection('backup');
        }
        return $this->connection;
    }
    /**
     * Get CREATE VIEW query for the specific view.
     *
     * @param string $viewName
     * @return string
     */
    private function get_create_view(string $view_name): string
    {
        $quoted_view_name = $this->get_connection()->quote_identifier($view_name);
        $query = 'SHOW CREATE VIEW ' . $quoted_view_name;
        $row = $this->get_connection()->fetch_row($query);
        $reg_exp = '/\sDEFINER\=\`([^`]*)\`\@\`([^`]*)\`/';
        $sql = preg_replace($reg_exp, '', $row['Create View']);
        return $sql . ';' . "\n";
    }
    /**
     * Prepare a header for View being dumped.
     *
     * @param string $viewName
     * @return string
     */
    public function get_view_header(string $view_name): string
    {
        $quoted_view_name = $this->get_connection()->quote_identifier($view_name);
        return "\n--\n" . "-- Structure for view {$quoted_view_name}\n" . "--\n\n";
    }
    /**
     * Make sure that View being created is deleted if already exists.
     *
     * @param string $viewName
     * @return string
     */
    public function get_drop_view_sql(string $view_name): string
    {
        $quoted_view_name = $this->get_connection()->quote_identifier($view_name);
        return sprintf("DROP VIEW IF EXISTS %s;\n", $quoted_view_name);
    }
}