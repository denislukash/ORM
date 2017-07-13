<?php

/**
 * Basics class for working with selections
 *
 * @package sql
 */
class Table
{
    /**
     * Table name
     *
     * @var string|null
     */
    protected $_tableName = null;

    /**
     * AdapterAbstract for work with database
     *
     * @var MySQLAdapter
     */
    protected $_adapt = null;

    /**
     * Table constructor create exemplar adapter with config, if param not set,
     * create default adapter
     *
     * @param AdapterAbstract $adapter
     */
    public function __construct($adapter)
    {
        $this->_adapt = $adapter;
        $this->_adapt->connect();
    }

    /**
     * Return adapter
     *
     * @return AdapterAbstract|null
     */
    public function getAdapter()
    {
        return $this->_adapt;
    }

    /**
     * Insert data to database
     *
     * @param array $data data for insert, value in data must be with key
     *                    'val' - values for insert and 'col' - columns name
     */
    public function insert(array $data)
    {
        $data['name'] = $this->_tableName;
        $this->_adapt->insert($data);
    }

    /**
     * Update table in database
     *
     * @param array $data column-value pairs
     * @param array|string $where WHERE condition
     * @param array $bind placeholders
     */
    public function update(array $data, $where, $bind = array())
    {
        $this->_adapt->update($this->_tableName, $data, $where, $bind);
    }

    /**
     * Delete row, column or value from it or table at all
     *
     * @param string|array $where condition for delete
     * @param array $bind placeholders
     */
    public function delete($where, $bind = array())
    {
        $this->_adapt->delete($this->_tableName, $bind, $where);
    }

    /**
     * Get result data in array
     *
     * @param null|array|string $where condition
     * @param null|string $order ORDER BY value
     * @param null|int $count LIMIT value
     * @param null|int $offset OFFSET value
     *
     * @return array data
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $request = $this->select()
            ->where($where)
            ->order($order, null, $offset)
            ->limit($count)
            ->getRequest();

        return $this->_adapt->fetchAll($request);
    }

    /**
     * Get one row from result data
     *
     * @param null|array|string $where condition
     * @param null|string $order ORDER BY value
     * @param null|int $offset OFFSET value
     *
     * @return array data
     */
    public function fetchRow($where = null, $order = null, $offset = null)
    {
        $request = $this->select()
            ->where($where)
            ->order($order, null, $offset)
            ->getRequest();

        return $this->_adapt->fetchRow($request);
    }

    public function select()
    {
        return (new Select())->from($this->_tableName);
    }
}