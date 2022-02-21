<?php

namespace Src\Controller;

use SplFileInfo;

class Manager
{
    private static $instance = null;
    private $filename = 'data.txt';
    /**
     * @var DataController|\Src\Methods\DbConnection|\Src\Methods\Query|null
     */
    private $controller;
    /**
     * @var void
     */
    protected $foldersTable;
    /**
     * @var int
     */
    private $position;
    /**
     * @var int
     */
    private $count;
    private $folders;
    public $subfoldersTable;
    /**
     * @var DataController
     */
    public $filesTable;
    /**
     * @var array|false|mixed
     */
    public $subFolders;
    /**
     * @var mixed
     */
    private $search;
    private $error;
    private $result;
    /**
     * @var SearchController|null
     */
    private $searchControl;

    /**
     * @var string
     */

    public function __construct()
    {
        $this->foldersTable = new DataController('Folders');
        $this->subfoldersTable = new DataController('SubFolders');
        $this->filesTable = new DataController('Files');
        $this->searchControl = SearchController::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Manager();
        }

        return self::$instance;
    }

    private function existsInArray($name, $array)
    {
        foreach ($array as $element) {
            if ($name == $element['name']) {
                return $element;
            }
        }
        return false;
    }


    public function loadData()
    {
        $file = fopen($this->filename, "r") or die("Unable to open file!");
        $docs = fread($file, filesize($this->filename));
        $docs = explode(',', $docs);
        foreach ($docs as $doc) {
            $folders_n_files = explode('/', $doc);
            $this->count = count($folders_n_files);
            $this->position = 0;
            $prev = '';
            $current = '';
            $this->recursiveManager($folders_n_files, $prev, $current);
        }
        echo "file upload complete";
    }

    private function recursiveManager(array $folders_n_files, $prev, $current)
    {
        $name = $folders_n_files[$this->position];
        if ($this->position === 0) {
            $prev = $current ?: null;
            $current = $this->saveFolder($name);
        } elseif ($this->position >= $this->count - 1) {
            $info = new SplFileInfo($name);
            $prev = $current ?: null;
            if ($info->getExtension()) {
                $current = $this->saveFile($name, $prev, $this->position);
            } else {
                $current = $this->saveSubFolder($name, $prev, $this->position);
            }
        } else {
            $prev = $current ?: null;
            $current = $this->saveSubFolder($name, $prev, $this->position);
        }
        if ($this->position < $this->count - 1) {
            $this->position++;
            $this->recursiveManager($folders_n_files, $prev, $current);
        }
    }

    private function saveFolder($name)
    {
        $this->folders = $this->foldersTable->fetchData();
        $data = [];
        $data['name'] = $name;
        if (!$this->existsInArray($name, $this->folders)) {
            $row = $this->foldersTable->create($data);
            return $row['lastInsertRow'];
        } else {
            $this->folders = $this->foldersTable->fetchData();
            return $this->existsInArray($name, $this->folders);
        }
    }

    private function saveFile($name, $prev, $position)
    {
        $data = [];
        $data['name'] = $name;
        $data['folder_id'] = $prev ? $prev['id'] : null;
        $this->folders = $this->filesTable->fetchData();
        if (!$this->existsInArray($name, $this->folders)) {
            $row = $this->filesTable->create($data);
            $result = $row['lastInsertRow'];
        } else {
            $this->folders = $this->filesTable->fetchData();
            $result = $this->existsInArray($name, $this->folders);
        }
        $data['id'] = $prev['id'];
        $data['sub_folder_id'] = $result['id'];
        $this->subfoldersTable->update($data);
        return $result;
    }

    private function saveSubFolder($name, $prev, $position)
    {
        $this->subFolders = $this->subfoldersTable->fetchData();
        $data = [];
        $data['position'] = $position;
        $data['name'] = $name;
        $data['folder_id'] = $prev ? $prev['id'] : null;
        if (!$this->existsInArray($name, $this->subFolders)) {
            $data['sub_folder_id'] = NULL;
            $row = $this->subfoldersTable->create($data);
            $result = $row['lastInsertRow'];
        } else {
            $this->subFolders = $this->subfoldersTable->fetchData();
            $result = $this->existsInArray($name, $this->subFolders);
        }
        if ($prev) {
            $data['id'] = $prev['id'];
            if ($result) {
                $data['sub_folder_id'] = $result['id'];
                $this->subfoldersTable->update($data);
            }
        }
        return $result;
    }

    public function search()
    {
        if (isset($_POST['search'])) {
            $this->search = $_POST['search'];
            if ($this->search === '') {
                $this->setSearchError('search for a file');
            } else {
                $result = $this->searchControl->queryDb($this->search);
                if ($result) {
                    $this->setSearchResult($result);
                } else {
                    $this->setSearchError('no result found');

                }

            }
        }
    }

    protected function setSearchError($string)
    {
        $this->error = $string;
    }

    protected function setSearchResult($result)
    {
        $this->result = $result;
    }

    public function getSearchError()
    {
        return $this->error;
    }

    public function getSearchResult()
    {
        return $this->result;
    }

}