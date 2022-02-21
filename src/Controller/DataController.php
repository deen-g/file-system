<?php

namespace Src\Controller;

use Src\Methods\Query;

class DataController
{
    /**
     * @var mixed|null
     */
    private $table;
    /**
     * @var Query|null
     */
    private $query;


    public function __construct($table = null)
    {
        $this->table = $table;
        $this->query = Query::getInstance();
    }

    public function create($data)
    {
        $error = "";
        $lastInsertRow = "";
        if ($this->table === 'Folders') {
            $sql = "INSERT INTO `{$this->table}` (`id`, `name`) VALUES (?, ?)";
            $arr = array(NULL, $data['name']);
        } else if ($this->table === 'SubFolders') {
            $sql = "INSERT INTO `{$this->table}` (`id`,`folder_id`,`sub_folder_id`,`position`, `name`) VALUES (?, ?,?,?,?)";
            $arr = array(NULL, $data['folder_id'], $data['sub_folder_id'], $data['position'], $data['name']);
        } else {
            $sql = "INSERT INTO `{$this->table}` (`id`, `folder_id`, `name`) VALUES (?, ?,?)";
            $arr = array(NULL, $data['folder_id'], $data['name']);
        }

        return $this->executor($sql, $arr, $error, $lastInsertRow);
    }

    public function update($data)
    {
        $error = "";
        $lastInsertRow = "";
        $sql = "UPDATE `{$this->table}` SET `sub_folder_id` = ? WHERE `{$this->table}`.`id` = ?";
        $arr = array($data['sub_folder_id'], $data['id']);
        return $this->executor($sql, $arr, $error, $lastInsertRow);
    }

    /**
     * @param $id
     * @return array|false|mixed
     */
    public function FindOne($id)
    {
        $data = [];
        $data['id'] = $id;
        $sql = "SELECT * FROM `{$this->table}` WHERE `id` =?";
        $arr = array($data['id']);
        return $this->query->fetch($sql, $arr);
    }

    /**
     * @param $data
     * @return array|false|mixed
     */
    public function searchBYName($data)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `name` LIKE '$data%'";
        return $this->query->fetchAll($sql);
    }

    /**
     * @return array|false|mixed
     */
    public function fetchData()
    {
        $sql = "SELECT * FROM `{$this->table}`";
        return $this->query->fetchAll($sql);
    }

    /**
     * @param $sql
     * @param array $arr
     * @param $error
     * @param $lastInsertRow
     * @return array
     */
    protected function executor($sql, array $arr, $error, $lastInsertRow)
    {
        if (!$this->query->execute($sql, $arr)) $error = $this->error;
        if ($this->query->lastInsertId) $lastInsertRow = $this->FindOne($this->query->lastInsertId);
        elseif ($this->query->rowCount == 0) $error = "no data inputted";
        /* response */
        return [
            "sts" => $error == "" ? "success" : "fail",
            "err" => $error,
            "msg" => "CREATE success",
            "lastInsertRow" => $lastInsertRow,
        ];
    }

}