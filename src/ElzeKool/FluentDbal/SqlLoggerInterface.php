<?php

namespace ElzeKool\FluentDbal;

/**
 * SQL Query logger interface. Allows monitoring and
 * profiling of queries
 *
 * @author Elze Kool <info@kooldevelopment.nl>
 **/
interface SqlLoggerInterface
{
    /**
     * Log SQL Query
     * 
     * @param string        $query      SQL Query
     * @param mixed[]       $parameters Provided parameters
     * @param int           $took       Time it took to execute statement     * 
     * @param \PDOStatement $statement  Raw PDO statement
     * 
     * @return void
     */
    public function log($query, $parameters, $took, \PDOStatement $statement);
    
}

?>
