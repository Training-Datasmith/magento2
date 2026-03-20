<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Resource_Model;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
use Magento\Framework\Model\Resource_Model\Db\Context as DbContext;
use Magento\Search\Model\Query;
use Magento\Search\Model\Query_Factory;
use Zend_Db_Expr;
/**
 * Catalog search recommendations resource model
 *
 * @api
 * @since 100.0.2
 */
class Recommendations extends Abstract_Db
{
    /**
     * @var Query
     */
    protected $_search_query_model;
    /**
     * Construct
     *
     * @param string $connectionName
     */
    public function __construct(Db_Context $context, Query_Factory $query_factory, $connection_name = null)
    {
        parent::__construct($context, $connection_name);
        $this->_search_query_model = $query_factory->create();
    }
    /**
     * Init main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_recommendations', 'id');
    }
    /**
     * Save search relations
     *
     * @param int $queryId
     * @param array $relatedQueries
     * @return $this
     */
    public function save_related_queries($query_id, $related_queries = []): static
    {
        $connection = $this->get_connection();
        $where_or = [];
        if (count($related_queries) > 0) {
            $where_or[] = implode(' AND ', [$connection->quote_into('query_id=?', $query_id), $connection->quote_into('relation_id NOT IN(?)', $related_queries)]);
            $where_or[] = implode(' AND ', [$connection->quote_into('relation_id = ?', $query_id), $connection->quote_into('query_id NOT IN(?)', $related_queries)]);
        } else {
            $where_or[] = $connection->quote_into('query_id = ?', $query_id);
            $where_or[] = $connection->quote_into('relation_id = ?', $query_id);
        }
        $where_cond = '(' . implode(') OR (', $where_or) . ')';
        $connection->delete($this->get_main_table(), $where_cond);
        $exists_related_queries = $this->get_related_queries($query_id);
        $needed_related_queries = array_diff($related_queries, $exists_related_queries);
        foreach ($needed_related_queries as $relation_id) {
            $connection->insert($this->get_main_table(), ['query_id' => $query_id, 'relation_id' => $relation_id]);
        }
        return $this;
    }
    /**
     * Retrieve related search queries
     *
     * @param int|array $queryId
     * @param bool $limit
     * @param bool $order
     * @return array
     */
    public function get_related_queries($query_id, $limit = false, $order = false)
    {
        $collection = $this->_search_query_model->get_resource_collection();
        $connection = $this->get_connection();
        $query_id_cond = $connection->quote_into('main_table.query_id IN (?)', $query_id);
        $collection->get_select()->join(['sr' => $collection->get_table('catalogsearch_recommendations')], '(sr.query_id=main_table.query_id OR sr.relation_id=main_table.query_id) AND ' . $query_id_cond)->reset(Select::COLUMNS)->columns(['rel_id' => $connection->get_check_sql('main_table.query_id=sr.query_id', 'sr.relation_id', 'sr.query_id')]);
        if (!empty($limit)) {
            $collection->get_select()->limit($limit);
        }
        if (!empty($order)) {
            $collection->get_select()->order($order);
        }
        return $connection->fetch_col($collection->get_select());
    }
    /**
     * Retrieve related search queries by single query
     *
     * @param string $query
     * @param int $searchRecommendationsCount
     * @return array
     */
    public function get_recommendations_by_query($query, array $params, $search_recommendations_count)
    {
        $this->_search_query_model->load_by_query_text($query);
        if (isset($params['store_id'])) {
            $this->_search_query_model->set_store_id($params['store_id']);
        }
        $related_queries_ids = $this->load_by_query($query, $search_recommendations_count);
        $related_queries = [];
        if (count($related_queries_ids)) {
            $connection = $this->get_connection();
            $main_table = $this->_search_query_model->get_resource_collection()->get_main_table();
            $select = $connection->select()->from(['main_table' => $main_table], ['query_text', 'num_results'])->where('query_id IN(?)', $related_queries_ids)->where('num_results > 0');
            $related_queries = $connection->fetch_all($select);
        }
        return $related_queries;
    }
    /**
     * Retrieve search terms which are started with $queryWords
     *
     * @param string $query
     * @param int $searchRecommendationsCount
     * @return array
     */
    protected function load_by_query($query, $search_recommendations_count)
    {
        $connection = $this->get_connection();
        $query_id = $this->_search_query_model->get_id();
        $related_queries = $this->get_related_queries($query_id, $search_recommendations_count, 'num_results DESC');
        if ($search_recommendations_count - count($related_queries) < 1) {
            return $related_queries;
        }
        $query_words = [$query];
        if ($query !== null && str_contains($query, ' ')) {
            $query_words = array_unique(array_merge($query_words, explode(' ', $query)));
            foreach ($query_words as $key => $word) {
                $query_words[$key] = trim($word);
                if (strlen($word) < 3) {
                    unset($query_words[$key]);
                }
            }
        }
        $like_condition = [];
        foreach ($query_words as $word) {
            $like_condition[] = $connection->quote_into('query_text LIKE ?', $word . '%');
        }
        $like_condition = implode(' OR ', $like_condition);
        $select = $connection->select()->from($this->_search_query_model->get_resource()->get_main_table(), ['query_id'])->where(new Zend_Db_Expr($like_condition))->where('store_id=?', $this->_search_query_model->get_store_id())->order('num_results DESC')->limit($search_recommendations_count + 1);
        $ids = $connection->fetch_col($select);
        if (!is_array($ids)) {
            $ids = [];
        }
        $key = array_search($query_id, $ids);
        if ($key !== false) {
            unset($ids[$key]);
        }
        $ids = array_unique(array_merge($related_queries, $ids));
        return array_slice($ids, 0, $search_recommendations_count);
    }
}