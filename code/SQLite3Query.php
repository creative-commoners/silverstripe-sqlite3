<?php

namespace SilverStripe\SQLite;

use SilverStripe\ORM\Connect\Query;
use SQLite3Result;

/**
 * A result-set from a SQLite3 database.
 */
class SQLite3Query extends Query
{

    /**
     * The SQLite3Connector object that created this result set.
     *
     * @var SQLite3Connector
     */
    protected $database;

    /**
     * The internal sqlite3 handle that points to the result set.
     *
     * @var SQLite3Result
     */
    protected $handle;

    /**
     * Hook the result-set given into a Query class, suitable for use by framework.
     * @param SQLite3Connector $database The database object that created this query.
     * @param SQLite3Result $handle the internal sqlite3 handle that is points to the resultset.
     */
    public function __construct(SQLite3Connector $database, SQLite3Result $handle)
    {
        $this->database = $database;
        $this->handle = $handle;
    }

    public function __destruct()
    {
        if ($this->handle) {
            $this->handle->finalize();
        }
    }

    public function seek($row)
    {
        $this->handle->reset();
        $i=0;
        while ($i <= $row && $result = @$this->handle->fetchArray(SQLITE3_ASSOC)) {
            $i++;
        }
        return $result;
    }

    /**
     * @todo This looks terrible but there is no SQLite3::get_num_rows() implementation
     */
    public function numRecords()
    {
        // Some queries are not iterable using fetchArray like CREATE statement
        if (!$this->handle->numColumns()) {
            return 0;
        }
        
        $this->handle->reset();
        $c=0;
        while ($this->handle->fetchArray()) {
            $c++;
        }
        $this->handle->reset();
        return $c;
    }

    public function nextRecord()
    {
        if ($data = $this->handle->fetchArray(SQLITE3_ASSOC)) {
            return $data;
        } else {
            return false;
        }
    }
}
