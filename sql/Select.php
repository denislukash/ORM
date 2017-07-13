<?php

/**
 * Class Select build sql request builder
 *
 * @package sql
 */
class Select
{
    /**
     * For save table names of which want to get information
     *
     * @var null|string|array
     */
    protected $_tables = null;

    /**
     * For save names columns of tables, for get specific information
     * from some column
     *
     * @var null|array
     */
    protected $_column = null;

    /**
     * For save part of request, which contains operator JOIN
     *
     * @var null|string
     */
    protected $_join = null;

    /**
     * For save part of request, which contains operator WHERE
     *
     * @var null|string
     */
    protected $_where = null;

    /**
     * For save part of request, which contains operator GROUP BY
     *
     * @var null|string
     */
    protected $_group = null;

    /**
     * For save part of request, which contains operator HAVING
     *
     * @var null|string
     */
    protected $_having = null;

    /**
     * For save part of request, which contains operator ORDER BY
     *
     * @var null|string
     */
    protected $_order = null;

    /**
     * For save part of request, which contains operator LIMIT
     *
     * @var null|string
     */
    protected $_limit = null;

    const JOIN_INNER = 'INNER';
    const SQL_STAR = '*';

    /**
     * First of calls method for build sql request
     *
     * @param array|string $tableName array with names of select table and them aliases, if we
     *                                     select from 1 table, aliases can be not set, if select from
     *                                     several tables, alias must be
     * @param array $columnName in default column name has value for select all column
     *                                     from table, if select from several tables, each column name
     *                                     must be set with alias
     * @return $this                       exemplar of object Select
     */
    public function from($tableName, $columnName = array(self::SQL_STAR))
    {
        if (gettype($tableName) === 'array') {
            foreach ($tableName as $alias => $table) {
                $this->_tables[] = "{$table} AS {$alias}";
            }

            foreach ($columnName as $column) {
                $this->_column[] = $column;
            }
        } else {
            $this->_tables = $tableName;

            foreach ($columnName as $column) {
                $this->_column[] = $tableName . '.' . $column;
            }
        }

        return $this;
    }

    /**
     * Add operator join to sql request
     *
     * @param array $joinData must contain first value in format key => value, where key is alias of table
     *                                  name (value), second value must contain connect for join, type of join has
     *                                  default value INNER JOIN, if want to change, set value with key ['type']
     * @param null|array $columnName optional parameter, if not set, selected all column from joined table
     * @return $this                    exemplar of object Select
     */
    public function join(array $joinData, $columnName = null)
    {
        $join = [
            'name' => current($joinData),
            'alias' => array_search(current($joinData), $joinData),
            'on' => $joinData[0],
            'type' => isset($joinData['type']) ? $joinData['type'] : self::JOIN_INNER
        ];

        if ($columnName) {
            foreach ($columnName as $column) {
                $this->_column[] = $join['alias'] . '.' . $column;
            }

        } else {
            $this->_column[] = $join['alias'] . '.' . self::SQL_STAR;
        }

        $this->_join = " {$join['type']} JOIN {$join['name']} AS {$join['alias']} ON {$join['on']}";

        return $this;
    }

    /**
     * Add condition WHERE to sql request
     *
     * @param string|array $conditions if condition only one, param get type string and bind - array with
     *                                  one value, if condition more that one, all values in condition array
     *                                  with the exception of first, must have key witch means AND or OR
     * @return $this                    exemplar of object Select
     */
    public function where($conditions)
    {
        if (gettype($conditions) === 'string') {
            $this->_where = " WHERE {$conditions}";

        } elseif (gettype($conditions) === 'array') {
            $resultCondition = " WHERE ";

            foreach ($conditions as $key => $condition) {
                if (gettype($key) === 'string') {
                    $resultCondition = $resultCondition . " {$key} $condition";

                } else {
                    $resultCondition = $resultCondition . $condition;
                }
            }
            $this->_where = $resultCondition;
        }

        return $this;
    }

    /**
     * Add operator GROUP BY to sql request
     *
     * @param string $columnName name of column for grouping
     * @return $this                exemplar of object Select
     */
    public function group(string $columnName)
    {
        $this->_group = " GROUP BY {$columnName}";

        return $this;
    }

    /**
     * Add operator HAVING to sql request
     *
     * @param string $con condition
     * @param string|null $value optional, if set, be replace instead of ? in condition
     * @return $this                exemplar of object Select
     */
    public function having(string $con, string $value = null)
    {
        if ($value) {
            $condition = str_replace('?', $value, $con);
            $this->_having = " HAVING {$condition}";

        } else {
            $this->_having = " HAVING {$con}";
        }

        return $this;
    }

    /**
     * Add operator ORDER BY to sql request
     *
     * @param string|null $columnName name of column for ordering
     * @param null $decrease optional, can be set true, order be DESC
     * @param null|int $offset optional, offset integer
     * @return $this                exemplar of object Select
     */
    public function order($columnName, $decrease = null, $offset = null)
    {
        if ($offset) {
            $this->_order = " ORDER BY {$columnName} OFFSET {$offset}";

        } elseif ($columnName) {
            $this->_order = " ORDER BY {$columnName}";
        }

        if ($decrease) {
            $this->_order = "{$this->_order} DESC";
        }

        return $this;
    }

    /**
     * Add operator LIMIT to sql request
     *
     * @param int|null $limit
     * @return $this exemplar of object Select
     */
    public function limit($limit)
    {
        if ($limit) {
            $this->_limit = " LIMIT {$limit}";
        }

        return $this;
    }

    /**
     * In the end of chain building request call this method and it return string of
     * sql request
     *
     * @return string sql request
     */
    public function getRequest()
    {
        $columns = implode(', ', $this->_column);

        if (gettype($this->_tables) === 'array') {
            $tables = implode(',', $this->_tables);

        } else {
            $tables = $this->_tables;
        }

        $resultRequest = "SELECT {$columns} FROM {$tables}{$this->_join}{$this->_where}{$this->_group}"
            . "{$this->_having}{$this->_order}{$this->_limit}";

        return $resultRequest;
    }
}