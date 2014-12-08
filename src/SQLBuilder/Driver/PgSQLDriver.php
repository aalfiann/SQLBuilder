<?php
namespace SQLBuilder\Driver;
use DateTime;
use Exception;
use RuntimeException;

class PgSQLDriver extends BaseDriver
{

    /**
     * Check driver optino to quote table name
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     * @return string table name with/without quotes.
     */
    public function quoteTableName($name) 
    {
        if ($this->quoteTable) {
            return '"' . $name . '"';
        }
        return $name;
    }

    /**
     * Check driver option to quote column name
     *
     * column quote can be configured by 'quote_column' option.
     *
     * @param string $name column name
     * @return string column name with/without quotes.
     */
    public function quoteColumn($name)
    {
        if ($c = $this->quoteColumn) {
            if (preg_match('/\W/',$name)) {
                return $name;
            }
            return '"' . $name . '"';
        }
        return $name;
    }
    
}
