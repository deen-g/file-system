<?php

namespace Src\Methods;

use PDO;

class DbConnection
{
    // Hold the class instance.
    private static $instance = null;
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $name = 'filesystem';

    // The db connection is established in the private constructor.
    /**
     * @var PDO
     */
    private $conn;

    private function __construct()
    {
        $this->conn = new PDO("mysql:host={$this->host};dbname={$this->name}", $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        echo "connected";
        echo "<br/>";
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DbConnection();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function createTables()
    {
        $this->conn->exec("
CREATE TABLE Folders
( id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(30) NOT NULL 
)");

        $this->conn->exec("
CREATE TABLE SubFolders
( id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
folder_id int(10) UNSIGNED,
sub_folder_id int(10) UNSIGNED, 
name VARCHAR(30) NOT NULL,
position INT DEFAULT 0,
    CONSTRAINT FK_SubFolderSubFolder FOREIGN KEY (folder_id)
    REFERENCES SubFolders(id)
 )");

        $this->conn->exec("
CREATE TABLE Files 
( id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
folder_id int(10) UNSIGNED, 
name VARCHAR(30) NOT NULL
)");
        echo "Tables created successfully";
        echo "<br/>";
    }

    public function dropTables()
    {
        $this->conn->exec('DROP TABLE IF EXISTS Files,SubFolders,Folders');
        echo "Tables dropped successfully";
        echo "<br/>";
    }
}