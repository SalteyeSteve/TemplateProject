<?php

namespace App\Controller;

use App\Entity\GenericEntity;
use App\Util\DB;
use Exception;

class GenericController
{
    /**
     * @var DB
     */
    public $db;
    /**
     * @var array
     */
    private $data;

    const DB_TABLE = 'genericTable';


    public function __construct()
    {
        $db = new DB();
        $this->db = $db;
        $this->init();
    }
    public function init()
    {
        try {
            $this->data = $this->db->getAllData($this::DB_TABLE);
        } catch (Exception $e) {
            $this->data = null;
        }

    }

    /**
     * Adds a row of data to the database
     * @param array $data
     * @throws Exception
     */
    public function addData(array $data)
    {
        $fields =  ['field1', 'field2', 'field3'];
        $this->db->insertData($this::DB_TABLE, $fields, $data);
    }

    /**
     * Returns a specific record found by id as json
     * define fields if specific data desired
     * @param int $id
     * @param string $fields
     * @return string|null
     * @throws Exception
     */
    public function getRecordById(int $id, $fields = '*'): ?string
    {
        return json_encode($this->db->selectData($this::DB_TABLE, [$fields], [$id], 'id = ?'));
    }

    /**
     * Update a record in db
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateRecord(array $data): bool
    {
        $params = ['field1 = ?, field2 = ?, field3 = ?'];
        $condition = 'id = ?';
        $values = $data;
        return $this->db->updateData($this::DB_TABLE, $params, $condition, $values);
    }

    /**
     * Delete a record by id
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteRecord(int $id): bool
    {
        $condition = 'id = ?';
        $values = [$id];
        return $this->db->deleteData($this::DB_TABLE, $condition, $values);
    }

    /**
     * Communicates with the frontend
     */
    public function showData(): void
    {
        if ($this->data === null || count($this->data) === 0) {
            print('No data found');
        } else {
            foreach ($this->data as $obj) {
                // creates the entity from data
                $entity = new GenericEntity($obj);
                // do stuff with entity
            }
        }

    }

}
