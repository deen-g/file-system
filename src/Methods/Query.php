<?php

namespace Src\Methods;

use Exception;

class Query
{
    static $instance = null;
    static $db = null;
    /**
     * @var int
     */
    public $rowCount;
    /**
     * @var false|string
     */
    public $lastInsertId;
    /**
     * @var string
     */
    protected $error;
    /**
     * @var \PDO
     */
    private $conn;

    public function __construct()
    {
        $conn_instance = DbConnection::getInstance();
        $conn_instance->dropTables();
        $conn_instance->createTables();
        $this->conn = $conn_instance->getConnection();

    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Query();
        }

        return self::$instance;
    }

    /**
     * @param $sql
     * @param $params
     * @return bool
     */
    function execute($sql, $params = null)
    {
        // insert, update or delete
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $this->rowCount = $stmt->rowCount();
            $this->lastInsertId = $this->conn->lastInsertId();
            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
    }

    function fetchAll($sql, $params = null, $all = true)
    {
        // select
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $this->rowCount = $stmt->rowCount();
            if ($all)
                return $stmt->fetchAll();
            else
                return $stmt->fetch();
        } catch (Exception $ex) {
            print_r($ex->getMessage());
            $this->error = $ex->getMessage();
            return false;
        }
    }

    /**
     * @param $sql
     * @param $params
     * @return array|false|mixed
     */

    function fetch($sql, $params = null)
    {
        // select
        return $this->fetchAll($sql, $params, false);
    }

}