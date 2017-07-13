<?php

/**
 * Class adapter
 *
 * @package adapter
 */
class MySQLAdapter extends AdapterAbstract
{

    /**
     * MySQLAdapter constructor.
     * @param array $param option for connect to bd
     */
    public function __construct(array $param)
    {
        parent::__construct($param);
    }

    /**
     * Get describe table
     *
     * @param string $tableName name of table
     * @param string|array|null $columnName optional, names of column
     * @return array names of table column
     */
    public function describeTable(string $tableName, $columnName = null)
    {
        if (gettype($columnName) === 'array') {
            $columnName = implode(',', $columnName);
        }

        return $this->_pdo->query("DESCRIBE {$tableName} {$columnName}")->fetchAll();
    }

    /**
     * Get one row from result selection
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array result of selection
     */
    public function fetchOne(string $sql, $bind = array())
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);

        return $stmt->fetch();
    }

    /**
     * Get result selection from 2 column like key - value
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array first column - key, second - value
     */
    public function fetchPairs($sql, $bind = array())
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * From result selection get value of first column specified line
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @param int $rowNumber number of fetch row
     * @return string value of column
     */
    public function fetchCol($sql, $rowNumber, $bind = array())
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchColumn($rowNumber);
    }

    /**
     * Get assoc array, where key - name of column
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array result of selection
     */
    public function fetchAssoc($sql, $bind = array())
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get row from result selection
     *
     * @param string $sql request
     * @param array $bind placeholder
     * @param null $fetchMode fetch style
     * @return mixed result of selection
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetch($fetchMode);
    }
}