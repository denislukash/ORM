<?php

/**
 * Class adapter
 *
 * @package adapter
 */
abstract class AdapterAbstract
{
    /**
     * Options for connect to database, null if not set
     *
     * @var array|null
     */
    protected $_param = null;

    /**
     * IDbAdapter settings
     *
     * @var string|null
     */
    protected $_dsn = null;

    /**
     * Flag for check connect to database
     *
     * @var bool
     */
    protected $_isConnect = false;

    /**
     * Exemplar of object pdo
     *
     * @var \PDO
     */
    protected $_pdo = null;

    /**
     * adapter constructor.
     * @param array $param parameters for connect to database
     */
    function __construct(array $param)
    {
        $this->_param = $param;
        $this->_dsn = "{$param['typeDatabase']}:host={$param['host']}"
            . ";dbname={$param['nameDatabase']};charset={$param['charset']}";
    }

    /**
     * Create object pdo and connect to database, if we have error, catch it and
     * displaying message of error
     */
    public function connect()
    {
        try {
            $this->_pdo = new \PDO($this->_dsn, $this->_param['user'], $this->_param['pass'], $this->_param['option']);
            if ($this->_pdo !== null) {
                $this->_isConnect = true;
            }
        } catch (\PDOException $error) {
            echo "Error IDbAdapter to database. {$error->getMessage()} <br>";
            die();
        }
    }

    /**
     * Check is database now connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->_isConnect;
    }

    /**
     * Get object of pdo
     *
     * @return null|object
     */
    public function getConnection()
    {
        return $this->_pdo;
    }

    /**
     * Close connection with database
     */
    public function closeConnection()
    {
        $this->_pdo = null;
        $this->_isConnect = false;
    }

    /**
     * Get settings of connection to database
     *
     * @return array settings of connecting to database
     */
    public function getConfig()
    {
        return $this->_param;
    }

    /**
     * Get ID oj last insert row, or value, with return object sequence
     *
     * @param object|null $obj object sequence
     * @return string id of last added element
     */
    public function lastInsertID($obj = null)
    {
        return $this->_pdo->lastInsertId($obj);
    }

    /**
     * Did request to database
     *
     * @param string $sql sql request
     * @return array data
     */
    public function query(string $sql)
    {
        $stmt = $this->_pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get describe table
     *
     * @param string $tableName name of table
     * @param string|array|null $columnName optional, names of column
     * @return array names of table column
     */
    abstract public function describeTable(string $tableName, $columnName = null);

    /**
     * Get one row from result selection
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array result of selection
     */
    abstract public function fetchOne(string $sql, $bind = array());

    /**
     * Get result selection from 2 column like key - value
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array first column - key, second - value
     */
    abstract public function fetchPairs($sql, $bind = array());

    /**
     * From result selection get value of first column specified line
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @param int $rowNumber number of fetch row
     * @return string value of column
     */
    abstract public function fetchCol($sql, $rowNumber, $bind = array());

    /**
     * Get assoc array, where key - name of column
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @return array result of selection
     */
    abstract public function fetchAssoc($sql, $bind = array());

    /**
     * Get row from result selection
     *
     * @param string $sql request
     * @param array $bind placeholder
     * @param null $fetchMode fetch style
     * @return mixed result of selection
     */
    abstract public function fetchRow($sql, $bind = array(), $fetchMode = null);

    /**
     * Get result of selection in array
     *
     * @param string $sql sql request
     * @param array $bind placeholder
     * @param null $fetchMode fetch style
     * @return array result of selection
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll($fetchMode);
    }

    /**
     * Insert data to database
     *
     * @param array $opt table name, column and values for insert.Column names and
     *                   value must have type array
     */
    public function insert(array $opt)
    {
        $tableName = $opt['name'];
        $columnName = implode(",", $opt['col']);

        foreach ($opt['val'] as $value) {
            $values[] = $value;
        }

        for ($i = 0; $i < count($values); $i++) {
            $placeholder[] = '?';
        }
        $placeholder = implode(',', $placeholder);

        $request = "INSERT INTO {$tableName} ({$columnName}) VALUES ({$placeholder})";
        $bind = $opt['val'];

        $this->_pdo->prepare($request)->execute($bind);
    }

    /**
     * Insert data to several tables
     *
     * @param array $opt tables names, column and values for insert in to several tables
     */
    public function multiInsert(array $opt)
    {
        $this->beginTransaction();

        foreach ($opt as $tableName => $columnAndValues) {
            $this->insert([
                'name' => $tableName,
                'val' => $columnAndValues[1],
                'col' => $columnAndValues[0]
            ]);
        }

        $this->commit();
    }

    /**
     * Update table in database
     *
     * @param string $tableName table name for update
     * @param array $columnValue column-value pairs
     * @param array|string $condition WHERE condition
     * @param array $bind placeholders
     */
    public function update(string $tableName, array $columnValue, $condition, $bind = array())
    {
        $values = implode(',', $columnValue);
        if (gettype($condition) === 'array') {
            $resultCondition = '';

            foreach ($condition as $key => $con) {
                if (gettype($key) === 'string') {
                    $resultCondition = $resultCondition . " {$key} $con";

                } else {
                    $resultCondition = $resultCondition . $con;
                }
            }
        } else {
            $resultCondition = $condition;
        }

        $sql = "UPDATE {$tableName} SET $values WHERE {$resultCondition}";

        $this->_pdo->prepare($sql)->execute($bind);
    }

    /**
     * Delete row, column or value from it or table at all
     *
     * @param string $tableName table name
     * @param string|array $condition condition for delete
     * @param array $bind placeholders
     * @internal param array $opt table name and data for deleting
     */
    public function delete(string $tableName, $bind = array(), $condition = null)
    {
        if (gettype($condition) === 'array') {
            $resultCondition = ' WHERE';

            foreach ($condition as $key => $con) {
                if (gettype($key) === 'string') {
                    $resultCondition = $resultCondition . " {$key} $con";

                } else {
                    $resultCondition = $resultCondition . $con;
                }
            }
        } else if ($condition) {
            $resultCondition = " WHERE {$condition}";

        } else {
            $resultCondition = null;
        }

        $sql = "DELETE FROM {$tableName}{$resultCondition}";

        $this->_pdo->prepare($sql)->execute($bind);
    }

    /**
     * Start transaction
     */
    public function beginTransaction()
    {
        $this->_pdo->beginTransaction();
    }

    /**
     * Commit changes in transaction
     */
    public function commit()
    {
        $this->_pdo->commit();
    }

    /**
     * Cancel changes in transaction
     */
    public function rollBack()
    {
        $this->_pdo->rollback();
    }
}