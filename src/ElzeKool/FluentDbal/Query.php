<?php

namespace ElzeKool\FluentDbal;

use ElzeKool\FluentDbal\Exception\DbalException;

/**
 * Database Query
 * 
 * Created by the FluentDbal Database Adaptor
 *
 * @author Elze Kool <info@kooldevelopment.nl>
 **/
class Query 
{

    /**
     * PDO Connection
     * @var \PDO
     */
    private $Pdo = null;

    /**
     * Type
     * @var string
     */
    private $Type = null;

    /**
     * Fields
     * @var string[]
     */
    private $Fields = array();

    /**
     * Where
     * @var string[]
     * */
    private $Where = array();

    /**
     * Field <-> Value
     * @var string[]
     */
    private $Values = array();

    /**
     * Parameters to bind
     * @var mixed[]
     */
    private $Parameters = array();

    /**
     * Limit
     * @var int[]
     */
    private $Limit = array();

    /**
     * From/To Table
     * @var string
     * */
    private $Table;

    /**
     * Joins
     * @var string[][]
     */
    private $Joins = array();

    /**
     * Custom SQL
     * @var string
     */
    private $CustomSQL = '';

    /**
     * Order By
     * @var string[]
     */
    private $OrderBy = array();

    /**
     * Group By
     * @var string[]
     */
    private $GroupBy = array();

    /**
     * Prepared statement
     * @var \PDOStatement
     */
    private $Prepared = null;

    /**
     * Select For Update
     * @var boolean
     */
    private $ForUpdate = false;

    /**
     * Constructor
     *
     * @internal Do not create directly, only \ElzeKool\FluentDbal\FluentDbal should
     * create a new Query
     *
     * @see \ElzeKool\FluentDbal\FluentDbal
     *
     * @param PDO     $pdo       PDO Connection
     */
    public function __construct(\PDO $pdo)
    {
        $this->Pdo = $pdo;
    }

    /**
     * Cast Query to string
     *
     * @return string
     * */
    public function __toString() 
    {
        return $this->getQuery();
    }

    /**
     * Get SQL for current Query
     * 
     * @return string SQL Query
     */
    private function getQuery() 
    {

        switch ($this->Type) {

            // Custom SQL
            case 'custom':
                return $this->CustomSQL;

            // Select
            case 'select':
                return
                    'SELECT ' .
                    join(', ', $this->Fields) .
                    ' FROM ' . $this->Table .
                    $this->sqlJoin() .
                    $this->sqlWhere() .
                    $this->sqlGroupBy() .
                    $this->sqlOrderBy() .
                    $this->sqlLimit() .
                    ($this->ForUpdate ? ' FOR UPDATE' : '');

            // Delete
            case 'delete':
                return
                    'DELETE ' .
                    join(', ', $this->Fields) .
                    ' FROM ' .
                    $this->Table .
                    $this->sqlJoin() .
                    $this->sqlWhere() .
                    $this->sqlLimit();

            // Insert/Replace share syntax
            case 'insert':
            case 'replace':
                return
                    strtoupper($this->Type) . ' INTO ' .
                    $this->Table .
                    ' SET ' .
                    join(',', $this->Values) .
                    $this->sqlJoin();

            // Update
            case 'update':
                return
                    'UPDATE ' .
                    $this->Table .
                    ' SET ' .
                    join(', ', $this->Values) .
                    $this->sqlWhere() .
                    $this->sqlOrderBy() .
                    $this->sqlLimit();

            default:
                return "ERROR: Query type not set/unimplemented";
        }
    }

    /**
     * Set Type, check if another type is already set
     *
     * @return void
     */
    private function setType($type) 
    {
        if ($this->Type === null) {
            $this->Type = $type;
        } else if ($this->Type != $type) {
            throw new DbalException('Query already started with another type');
        }
    }

    /**
     * Return WHERE part of SQL query
     *
     * @return string Where part
     * */
    private function sqlWhere()
    {
        return (count($this->Where) == 0) ? '' : (' WHERE ' . join(' AND ', $this->Where));
    }

    /**
     * Return LIMIT part of SQL query
     *
     * @return string Limit part
     * */
    private function sqlLimit()
    {
        return (count($this->Limit) == 0) ? '' : (' LIMIT ' . join(',', $this->Limit));
    }

    /**
     * Return ORDER BY part of SQL query
     *
     * @return string Order By part
     * */
    private function sqlOrderBy() 
    {
        return (count($this->OrderBy) == 0) ? '' : (' ORDER BY ' . join(', ', $this->OrderBy));
    }

    /**
     * Return GROUP BY part of SQL query
     *
     * @return string Order By part
     * */
    private function sqlGroupBy() 
    {
        return (count($this->GroupBy) == 0) ? '' : (' GROUP BY ' . join(', ', $this->GroupBy));
    }

    /**
     * Return Joins part of SQL query
     *
     * @return string Join part
     * */
    private function sqlJoin() 
    {
        if (count($this->Joins) == 0) {
            return '';
        }
        $sql = '';
        foreach ($this->Joins as $join) {
            $sql .= ' ' . $join[0] . ' JOIN ' . $join[1] . ' ON ' . $join[2];
        }
        return $sql;
    }

    /**
     * Custom SQL
     *
     * Accepts more parameters for position based parameters
     *
     * @param string $sql Custom SQL
     *
     * @return Query
     * */
    public function custom($sql) 
    {
        $this->Prepared = null;
        $this->CustomSQL = $sql;
        $this->setType('custom');

        if (func_num_args() > 1) {
            for ($x = 1; $x < func_num_args(); $x++) {
                $this->Parameters[] = func_get_arg($x);
            }
        }

        return $this;
    }

    /**
     * Select
     *
     * @param string|string[] $fields     Fields to select
     * @param boolean         $for_update Select FOR UPDATE
     * 
     * @return Query
     * */
    public function select($fields, $for_update = false) {
        $this->Prepared = null;

        $this->setType('select');
        $this->Fields = array_merge($this->Fields, (array) $fields);
        $this->ForUpdate = $for_update;

        return $this;
    }

    /**
     * Insert
     *
     * @return Query
     * */
    public function insert() 
    {
        $this->Prepared = null;
        $this->setType('insert');
        return $this;
    }

    /**
     * Replace
     *
     * @return Query
     * */
    public function replace() 
    {
        $this->Prepared = null;
        $this->setType('replace');
        return $this;
    }

    /**
     * Update
     *
     * @return Query
     * */
    public function update() 
    {
        $this->Prepared = null;
        $this->setType('update');
        return $this;
    }

    /**
     * Delete
     *
     * @param string $fields Tables to delete (in case of JOIN)
     *
     * @return Query
     * */
    public function delete($fields = '')
    {
        $this->Prepared = null;
        $this->setType('delete');
        $this->Fields = array_merge($this->Fields, (array) $fields);
        return $this;
    }

    /**
     * Set table to select/delete from
     *
     * @param string $table Table
     *
     * @return Query
     * */
    public function from($table) 
    {
        $this->Prepared = null;
        if (($this->Type != 'select') AND ($this->Type != 'delete')) {
            throw new DbalException('From only allowed for select/delete queries');
        }
        $this->Table = $table;
        return $this;
    }

    /**
     * Set table to update/insert/replace into
     *
     * @param string $table Table
     *
     * @return Query
     * */
    public function into($table) 
    {
        $this->Prepared = null;

        if (($this->Type != 'update') AND ($this->Type != 'insert') AND ($this->Type != 'replace')) {
            throw new DbalException('Into only allowed for update/insert/replace queries');
        }
        $this->Table = $table;
        return $this;
    }

    /**
     * Set/Add where conditions
     *
     * Accepts more parameters for position based parameters
     *
     * @param string|string[] $conditions Conditions
     *
     * @return Query
     * */
    public function where($conditions)
    {
        $this->Prepared = null;
        $this->Where = array_merge($this->Where, (array) $conditions);
        if (func_num_args() > 1) {
            for ($x = 1; $x < func_num_args(); $x++) {
                $this->Parameters[] = func_get_arg($x);
            }
        }
        return $this;
    }

    /**
     * Set Limit
     *
     * @param int $count  Count
     * @param int $offset Offset
     *
     * @return Query
     */
    public function limit($count, $offset = 0) 
    {
        $this->Prepared = null;
        $this->Limit = array(
            $offset,
            $count
        );
        return $this;
    }

    /**
     * Add Left Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return Query
     */
    public function leftJoin($table, $on) 
    {
        return $this->join('LEFT', $table, $on);
    }

    /**
     * Add Right Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return Query
     */
    public function rightJoin($table, $on)
    {
        return $this->join('RIGHT', $table, $on);
    }

    /**
     * Add Inner Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return Query
     */
    public function innerJoin($table, $on)
    {
        return $this->join('INNER', $table, $on);
    }

    /**
     * Add Outer Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return Query
     */
    public function outerJoin($table, $on)
    {
        return $this->join('OUTER', $table, $on);
    }

    /**
     * Add Join
     *
     * @param string $type  Type
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return Query
     */
    public function join($type, $table, $on)
    {
        $this->Prepared = null;
        if (!in_array($type, array('LEFT', 'RIGHT', 'INNER', 'OUTER'))) {
            throw new DbalException('Invalid JOIN type');
        }
        if (!in_array($this->Type, array('select', 'delete', 'update'))) {
            throw new DbalException('Join only allowed for select/update/delete queries');
        }
        $this->Joins[] = array(
            $type,
            $table,
            $on
        );
        return $this;
    }

    /**
     * Add Field <-> Value
     *
     * Accepts more parameters for position based parameters
     *
     * @param string|string[] $values Field <-> Value combination(s)
     *
     * @return Query
     * */
    public function set($values) 
    {
        $this->Prepared = null;
        if (!in_array($this->Type, array('insert', 'update'))) {
            throw new DbalException('Set only allowed for insert/update queries');
        }
        $this->Values = array_merge($this->Values, (array) $values);
        if (func_num_args() > 1) {
            for ($x = 1; $x < func_num_args(); $x++) {
                $this->Parameters[] = func_get_arg($x);
            }
        }
        return $this;
    }

    /**
     * Order By
     *
     * @param string $field     Field
     * @param string $direction Direction (ASC|DESC)
     *
     * @return Query
     */
    public function orderby($field, $direction = 'ASC') 
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, array('ASC', 'DESC'))) {
            throw new DbalException('Invalid direction for Order By');
        }
        $this->OrderBy[] = $field . ' ' . $direction;
        return $this;
    }

    /**
     * Group By
     *
     * @param string $field Field
     *
     * @return Query
     */
    public function groupby($field) 
    {
        $this->GroupBy[] = $field;
        return $this;
    }

    /**
     * Execute Query and return result
     *
     * @param mixed[] $params Override params
     *
     * @return \PDOStatement Executed statement
     */
    public function execute($params = null) 
    {

        // Check if there is a prepared statement
        if ($this->Prepared === null) {
            $this->Prepared = $this->Pdo->prepare($this->getQuery());
            $this->Prepared->setFetchMode(\PDO::FETCH_OBJ);
        } else {
            // Make sure cursor is closed
            $this->Prepared->closeCursor();
        }

        // Check which parameters to use
        if ($params === null) {
            $params = $this->Parameters;
        }

        if (!$this->Prepared->execute($params)) {
            $error = $this->Prepared->errorInfo();
            $exception = new DbalException('[' . $error[0] . '] ' . $error[2]);
            $exception->setPdoStatement($this->Prepared);
            throw $exception;
        }

        return $this->Prepared;
    }

}
