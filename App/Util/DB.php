<?php

namespace App\Util;

use Exception;
use mysqli;

class DB extends mysqli
{
    private $last_id;

    public function __construct()
    {
        parent::__construct(DBHOST, DBUSER, DBPWD, DBNAME);

        if (!($this instanceof \mysqli)) {
            throw new \mysqli_sql_exception("Could not establish connection to the database");
        }
    }

    /**
     * @param string $table
     * @return array|bool
     * @throws Exception
     */
    public function getAllData(string $table)
    {
        if (strlen($table) === 0) {
            throw new Exception('No table name was provided');
        }
        $sql = 'SELECT * FROM '.$table;

        $query = $this->quickPrepare($sql, []);

        return $query;
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $values
     * @param string|null $condition
     * @return array|bool
     * @throws Exception
     */
    public function selectData(string $table, array $fields = ['*'], array $values = [], string $condition = null)
    {
        if (strlen($table) === 0) {
            throw new Exception('No table/condition was provided');
        }

        $where = '';
        if ($condition !== null && strlen($condition) > 0) {
            $where = ' WHERE '.$condition;
        }
        // get the right params string
        count($fields) > 1 ? $fields = implode(', ', $fields) : $fields = $fields[0];

        $sql = 'SELECT '.$fields.' FROM '.$table.$where;
        $query = $this->quickPrepare($sql, $values);
        return $query;
    }

    /**
     * Simple update function that wraps the update sql query
     * @param string $table Table name
     * @param array $params [id = ?, videoUrl = ?, etc]
     * @param string $condition videoId = ?
     * @param array $values
     * @return bool
     * @throws Exception
     */
    public function updateData(string $table, array $params, string $condition, array $values) : bool
    {
        if (strlen($table) === 0 || strlen($condition) === 0) {
            throw new Exception('No table/condition was provided');
        }
        if (count($params) === 0) {
            throw new Exception('No parameters was provided');
        }

        $sql = 'UPDATE '.$table.' SET '.implode(', ', $params).' WHERE '.$condition;

        $query = $this->quickPrepare($sql, $values);

        if (!$query) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array $values
     * @return bool
     * @throws Exception
     */
    public function deleteData(string $table, string $condition, array $values)
    {
        if (strlen($table) === 0 || strlen($condition) === 0) {
            throw new Exception('No table/condition was provided');
        }

        $sql = 'DELETE FROM '.$table.' WHERE '.$condition;
        $query = $this->quickPrepare($sql, $values);

        if (!$query) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $values
     * @return bool
     * @throws Exception
     */
    public function insertData(string $table, array $fields, array $values)
    {
        if (strlen($table) === 0) {
            throw new Exception('No table/condition was provided');
        }
        if (count($fields) === 0) {
            throw new Exception('No parameters was provided');
        }
        $questionmarks = '';

        for ($i = 0; $i < count($fields); $i++) {
            if ($i < count($fields) - 1) {
                $questionmarks .= '?, ';
            } else {
                $questionmarks .= '?';
            }
        }
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', $fields).') VALUES ('.$questionmarks.')';
        $query = $this->quickPrepare($sql, $values);

        if (!$query) {
            return false;
        } else {
            return $this->last_id;
        }
    }

    /**
     * A fast way of prepared statements, you are welcome :)
     * @param $sql
     * @param array $values
     * @param null $types
     * @param bool $stmt
     * @return array|bool
     */
    public function quickPrepare($sql, array $values = [], $types = null, $stmt = false)
    {
        $values_prepared = $this->buildPrepareQuery($sql, $values, $types);
        if ($values_prepared === false || !is_array($values_prepared)) {
            return false;
        }

        $sql = $values_prepared['sql'];
        $values_prepared = $values_prepared['values'];

        $value_list = [];
        foreach (array_keys($values_prepared) as $key) {
            $value_list[] = &$values_prepared[$key];
        }

        if ($stmt === false) {
            $stmt = $this->prepare($sql);
            if (!$stmt) {
                return false;
            }
        }

        if (count(array_filter($value_list)) > 0) {
            call_user_func_array([$stmt, 'bind_param'], $value_list);
        }

        $result = $stmt->execute();
        if (!$result) {
            return false;
        }

        $stmt->store_result();
        $row = [];
        $data = $stmt->result_metadata();

        if (!$data) {
            $this->last_id = $stmt->insert_id;
            //UPDATE or INSERT so return \mysqli_stmt
            return $result;
        }

        $fields = [];
        $fields [0] = &$stmt;
        while ($field = $data->fetch_field()) {
            $fields [] = &$row [$field->name];
        }
        $return = [];

        call_user_func_array("mysqli_stmt_bind_result", $fields);
        /* fetch values */
        $i = 0;
        while ($stmt->fetch()) {
            foreach ($row as $key1 => $value1) {
                $return [$i] [$key1] = $value1;
            }
            $i++;
        }

        $stmt->free_result();

        return $return;
    }

    /**
     * Good stuff here, don't look
     * @param $sql
     * @param array $values
     * @param null $types
     * @return array|bool
     */
    private function buildPrepareQuery($sql, array $values, $types = null)
    {
        $org_sql = $sql;
        $order_values = [];

        $value_list = [];
        $string_types = [];

        if (is_null($types)) {
            foreach ($values as $value) {
                if (is_float($value)) {
                    $types .= 'd';
                } elseif (is_int($value)) {
                    $types .= 'i';
                } else {
                    $types .= 's';
                }
            }
        }

        if (strlen($types) !== count($values)) {
            //throw exception!
            return false;
        }

        $num = 0;
        foreach ($values as $key => $value) {
            if (!is_numeric($key)) {
                //find position of the value since we need to order the values array in that order
                $count = 0;
                $regex = '/:' . $key . '([\s,\)]{1}|\z)/';
                $sql = preg_replace($regex, '?$1', $sql, -1, $count);
                $value_list [$key] = &$values [$key];

                if (preg_match_all($regex, $org_sql, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $pos) {
                        $pos = $pos[1]; //offset of the match
                        $order_values[$pos] = $key;
                        $string_types[$pos] = $types[$num];
                    }
                }

            } else {
                $value_list[] = $value;
                $string_types[] = $types[$num];
            }
            $num++;
        }

        ksort($order_values);
        $order_values = array_flip($order_values);

        $value_list = array_values(array_merge($order_values, $value_list));

        ksort($string_types);
        $string_types = implode('', $string_types);

        array_unshift($value_list, $string_types);

        return ['sql' => $sql, 'values' => $value_list];

    }

    /**
     * Gets last id that was inserted into the database
     * @return mixed
     */
    public function lastId()
    {
        if ($this->last_id) {
            return $this->last_id;
        } else {
            return $this->last_id;
        }
    }
}