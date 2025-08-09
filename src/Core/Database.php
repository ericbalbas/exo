<?php

namespace App\Core;

/**
 * Interface Queries
 * Defines contract for database operations.
 */
interface Queries
{

    public function getIntances() ;

    /**
     * Set the table name to operate on.
     *
     * @param string $table Table name
     * @return $this
     */
    public function table($table);

    /**
     * Set values for insert/update.
     *
     * @param array $data Associative array of field => value
     * @return $this
     */
    public function setValues(array $data);

    /**
     * Execute a query of given type.
     *
     * @param string $type Query type: 'insert', 'update', 'delete', 'select'
     * @return mixed Query result or false on failure
     * @throws \Exception on SQL error
     */
    public function execute($type);

    /**
     * Add a where clause.
     *
     * @param mixed $field Field name or array of values for IN condition
     * @param mixed|null $value Value or array of values
     * @return $this
     */
    public function where($field, $value = null);

    /**
     * Set columns to select.
     *
     * @param string|array $columns Columns or comma-separated string
     * @return $this
     */
    public function setColumn($columns);

    /**
     * Set order by clause.
     *
     * @param string|array $columns Column(s) to order by
     * @param string $sort 'ASC' or 'DESC'
     * @return $this
     */
    public function orderBy($columns, $sort = 'ASC');

    /**
     * Set group by clause.
     *
     * @param string|array $columns Column(s) to group by
     * @return $this
     */
    public function groupBy($columns);

    /**
     * Set limit and optional offset.
     *
     * @param int $count Number of rows to limit
     * @param int|null $offset Offset for limit
     * @return $this
     */
    public function limit($count, $offset = null);

    /**
     * Display an array or object in readable format.
     *
     * @param array|object $array Data to display
     * @return void
     */
    public static function display($array);

    /**
     * Execute raw SQL and fetch results.
     *
     * @param string $sql Raw SQL query
     * @param int $type 0 for fetch results, 1 for count rows
     * @return array|int
     * @throws \Exception on SQL error
     */
    public static function fetchSql($sql, int $type);
}

/**
 * Class Database
 * Implements basic CRUD operations using mysqli.
 */
class Database implements Queries
{
    private $DBName = "";
    private $DBServer = "";
    private $DBUser = "";
    private $DBPass = "";

    /** @var \mysqli $db Database connection */
    public $db;

    /** @var string $tableName Table to operate on */
    protected $tableName;

    /** @var array $data Field values */
    protected $data = [];

    /** @var array $fields Fields to insert/update */
    protected $fields = [];

    /** @var string $generatedQuery Last generated SQL */
    private $generatedQuery;

    /** @var array $whereClauses Array of WHERE clauses */
    private $whereClauses = [];

    /** @var array $results Query results */
    private $results = [];

    /** @var string $column Columns to select */
    private $column = "*";

    /** @var string $orderBy ORDER BY clause */
    private $orderBy = '';

    /** @var string $groupBy GROUP BY clause */
    private $groupBy = '';

    /** @var string $limit LIMIT clause */
    private $limit = '';

    /**
     * Database constructor.
     * Initializes connection parameters and connects.
     */
    public function __construct()
    {
        $this->DBServer = "";
        $this->DBUser = "";
        $this->DBPass = "";
        $this->DBName = "";
        $this->getConnection();
        $this->db->set_charset("utf8mb4");
    }

    /**
     * Establish MySQLi connection.
     *
     * @return void
     * @throws \ErrorException If connection fails
     */
    private function getConnection()
    {
        $this->db = new \mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
        $this->db->set_charset("utf8");
        if ($this->db->connect_error) {
            trigger_error('Database connection failed: '  . $this->db->connect_error, E_USER_ERROR);
        }

    }

    public function getIntances()
    {
        return $this->db;
    }

    /**
     * Display an array or object in a formatted way.
     *
     * @param array|object $array Data to display
     * @return void
     *
     * @example
     * Database::display($data);
     */
    public static function display($array)
    {
        echo "<pre>" . print_r($array, true) . "</pre>";
    }

    /**
     * Execute a raw SQL query and fetch results.
     *
     * @param string $sql Raw SQL query string
     * @param int $type 0 = fetch all rows, 1 = return count of rows
     * @return array|int Result set or row count
     * @throws \Exception On SQL error
     *
     * @example
     * $rows = Database::fetchSql("SELECT * FROM users", 0);
     * $count = Database::fetchSql("SELECT * FROM users", 1);
     */
    public static function fetchSql($sql, int $type = 0)
    {
        $db = new self();
        $db->getConnection();
        $db->db->set_charset("utf8mb4");

        $result = $db->db->query($sql);

        if ($result === false) {
            throw new \Exception("SQL Error: " . $sql . " - " . $db->db->error);
        }

        if ($type == 1) {
            return $result->num_rows;
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = (object) $row;
        }

        return $data;
    }

    /**
     * Set the table for queries.
     *
     * @param string $table Table name
     * @return $this
     *
     * @example
     * $db->table('users');
     */
    public function table($table)
    {
        $this->tableName = $table;
        return $this;
    }

    /**
     * Set field values for insert or update.
     *
     * @param array $data Associative array of fields and values
     * @return $this
     *
     * @example
     * $db->setValues(['name' => 'John', 'email' => 'john@example.com']);
     */
    public function setValues(array $data)
    {
        $this->data = $data;
        $this->fields = array_keys($data);
        return $this;
    }

    /**
     * Set columns to select.
     *
     * @param string|array $columns Columns to select, e.g. 'id, name' or ['id', 'name']
     * @return $this
     *
     * @example
     * $db->setColumn('id, name');
     */
    public function setColumn($columns)
    {
        $this->column  = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    /**
     * Add WHERE clause(s) to the query.
     *
     * @param mixed $field Field name or array of values for IN
     * @param mixed|null $value Value or array of values
     * @return $this
     *
     * @throws \InvalidArgumentException When search value missing
     *
     * @example
     * $db->where('id', 5);
     * $db->where('status', [1, 2, 3]);
     * $db->where(['active', 'verified']);
     */
    public function where($field, $value = null)
    {
        if (is_array($field)) {
            // IN clause with values in $field array
            $values = implode(', ', array_map(function ($val) {
                return is_numeric($val) ?
                    $this->db->real_escape_string($val) :
                    "'" . $this->db->real_escape_string($val) . "'";
            }, $field));
            $this->whereClauses[] = "$field IN ($values)";
        } else {
            // Check for comparison operators in the field
            $operator = '=';
            if (preg_match('/(.*)\s+(=|!=|>|<|>=|<=|LIKE)$/i', $field, $matches)) {
                $field = trim($matches[1]);
                $operator = strtoupper(trim($matches[2]));
            }

            if (is_array($value)) {
                // IN clause with values in $value array
                $values = implode(', ', array_map(function ($val) {
                    return is_numeric($val) ? $val : "'" . $val . "'";
                }, $value));
                $this->whereClauses[] = "$field IN ($values)";
            } elseif (is_numeric($value)) {
                $this->whereClauses[] = "$field $operator $value";
            } else {
                if (!isset($value) || $value === '') {
                    throw new \InvalidArgumentException("Search value for '$field' is missing or empty.");
                }

                $escapedValue = $this->db->real_escape_string($value);
                if ($operator === 'LIKE') {
                    $this->whereClauses[] = "$field LIKE '%$escapedValue%'";
                } else {
                    $this->whereClauses[] = "$field $operator '$escapedValue'";
                }
            }
        }

        return $this;
    }


    /**
     * Set ORDER BY clause.
     *
     * @param string|array $columns Column(s) to order by
     * @param string $sort 'ASC' or 'DESC' (default 'ASC')
     * @return $this
     *
     * @example
     * $db->orderBy('id', 'DESC');
     * $db->orderBy(['name', 'created_at']);
     */
    public function orderBy($columns, $sort = 'ASC')
    {
        $orderBy = is_array($columns) ? implode(', ', $columns) : $columns;
        $this->orderBy = " ORDER BY $orderBy $sort";
        return $this;
    }

    /**
     * Set GROUP BY clause.
     *
     * @param string|array $columns Column(s) to group by
     * @return $this
     *
     * @example
     * $db->groupBy('category');
     * $db->groupBy(['category', 'status']);
     */
    public function groupBy($columns)
    {
        $groupBy = is_array($columns) ? implode(', ', $columns) : $columns;
        $this->groupBy = " GROUP BY $groupBy";
        return $this;
    }

    /**
     * Set LIMIT clause.
     *
     * @param int $count Number of records to fetch
     * @param int|null $offset Offset, optional
     * @return $this
     *
     * @example
     * $db->limit(10);
     * $db->limit(10, 20);
     */
    public function limit($count, $offset = null)
    {
        $this->limit = $offset !== null ? " LIMIT $offset, $count" : " LIMIT $count";
        return $this;
    }

    /**
     * Generate SQL INSERT query.
     *
     * @return string SQL query string
     */
    private function insert()
    {
        $fields = implode(", ", $this->fields);
        $escapedValues = array_map(function ($value) {
            return $this->db->real_escape_string($value);
        }, $this->data);
        $values = "'" . implode("', '", $escapedValues) . "'";
        $query = "INSERT INTO {$this->tableName} ($fields) VALUES ($values)";

        return $query;
    }

    /**
     * Generate SQL UPDATE query.
     *
     * @return string|false SQL query string or false if data/where not set
     */
    private function update()
    {
        if (empty($this->data) || empty($this->whereClauses)) {
            return false;
        }

        $setClause = $this->buildSetClause();
        $whereClause = implode(" AND ", $this->whereClauses);

        $query = "UPDATE {$this->tableName} SET $setClause WHERE $whereClause";

        return $query;
    }

    /**
     * Generate SQL DELETE query.
     *
     * @return string|false SQL query string or false if no where clauses
     */
    private function delete()
    {
        if (empty($this->whereClauses)) {
            return false;
        }

        $whereClause = implode(" AND ", $this->whereClauses);
        $query = "DELETE FROM {$this->tableName} WHERE $whereClause";

        return $query;
    }

    /**
     * Generate SQL SELECT query.
     *
     * @return string SQL query string
     */
    private function select()
    {
        $whereClause = !empty($this->whereClauses) ? "WHERE " . implode(" AND ", $this->whereClauses) : '';
        $orderByClause = $this->orderBy;
        $groupByClause = $this->groupBy;
        $limitClause = $this->limit;

        $query = "SELECT {$this->column} FROM {$this->tableName} $whereClause $groupByClause $orderByClause $limitClause";

        return $query;
    }

    /**
     * Helper to build SET clause for UPDATE.
     *
     * @return string SET clause like "field1 = 'value1', field2 = 'value2'"
     */
    private function buildSetClause()
    {
        $setClause = [];
        foreach ($this->fields as $field) {
            $escapedValue = $this->db->real_escape_string($this->data[$field]);
            $setClause[] = "$field = '$escapedValue'";
        }
        return implode(", ", $setClause);
    }

    /**
     * Execute a query of a given type.
     *
     * @param string $type Type of query: 'insert', 'update', 'delete', 'select'
     * @return mixed Result set for select, true/false for others, or false on failure
     * @throws \Exception On SQL error
     *
     * @example
     * $db->table('users')->setValues(['name' => 'John'])->execute('insert');
     * $db->table('users')->where('id', 1)->setValues(['name' => 'Jane'])->execute('update');
     * $db->table('users')->where('id', 1)->execute('delete');
     * $db->table('users')->where('id', 1)->execute('select');
     */
    public function execute($type)
    {
        if (!$this->tableName || empty($this->data) && in_array($type, ['insert', 'update'])) {
            return false;
        }

        switch ($type) {
            case 'insert':
                $query = $this->insert();
                break;
            case 'update':
                $query = $this->update();
                if ($query === false) {
                    return false;
                }
                break;
            case 'delete':
                $query = $this->delete();
                if ($query === false) {
                    return false;
                }
                break;
            case 'select':
                $query = $this->select();
                break;
            default:
                return false; // Invalid operation
        }

        $this->generatedQuery = $query;

        $sql = $this->db->query($query);

        if ($sql === false) {
            throw new \Exception("SQL Error: " . $query . " - " . $this->db->error);
        }

        if ($type === 'select') {
            $results = [];
            while ($row = $sql->fetch_assoc()) {
                $results[] = (object) $row;
            }

            if (count($results) < 1) {
                return "No results found.";
            }

            return $results;
        }

        return $sql;
    }

    /**
     * Echo the last generated SQL query string.
     *
     * @return void
     *
     * @example
     * $db->getGeneratedQuery();
     */
    public function getGeneratedQuery()
    {
        echo $this->generatedQuery;
    }
}
