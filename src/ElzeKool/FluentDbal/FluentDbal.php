<?php

namespace ElzeKool\FluentDbal;

use ElzeKool\FluentDbal\Query;

/**
 * Easy to use and framework independant fluent database abstraction layer on top of PDO
 *
 * @author Elze Kool <info@kooldevelopment.nl>
 **/
class FluentDbal
{
    
    /**
     * PDO Connection
     * @var \PDO
     */
    private $Pdo;

    /**
     * Constructor
     * 
     * @param \PDO $pdo PDO instance
     */
    public function __construct(\PDO $pdo) 
    {
        $this->Pdo = $pdo;
    }
    
    /**
     * Check if within an transaction
     *
     * @return boolean In Transaction
     **/
    public function isInTransaction() 
    {
        $in_trans = $this->Pdo->inTransaction();
        // Resolve following bug:
        // https://bugs.php.net/bug.php?id=62685
        return (($in_trans === true) OR ($in_trans === 1));
    }

    /**
     * Begin new transaction
     * 
     * @return boolean Success
     */
    public function beginTransaction() 
    {
        return $this->Pdo->beginTransaction();
    }
    
    /**
     * Rollback transaction
     * 
     * @return boolean Success
     */
    public function rollbackTransaction() 
    {
        return $this->Pdo->rollBack();
    }
    
    /**
     * Commit transaction
     * 
     * @return boolean Success
     */
    public function commitTransaction() 
    {
        return $this->Pdo->commit();
    }
    
    /**
     * Create new Query
     * 
     * @return Query Query
     */
    public function newQuery() 
    {
        return new Query($this->Pdo);
    }
    
    /**
     * Return last inserted 
     * 
     * @param string $name Name of field
     * 
     * @return void
     */
    public function getLastInsertedId($name = null) 
    {
        return $this->Pdo->lastInsertId($name);        
    }
    
}
