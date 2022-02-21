<?php

namespace Src\Controller;

class SearchController
{
    private static $instance = null;

    // The constructor is private
    // to prevent initiation with outer code.
    /**
     * @var DataController
     */
    private $foldersTable;
    /**
     * @var DataController
     */
    private $subfoldersTable;
    /**
     * @var DataController
     */
    private $filesTable;

    private function __construct()
    {
        // The expensive process (e.g.,db connection) goes here.
        $this->foldersTable = new DataController('Folders');
        $this->subfoldersTable = new DataController('SubFolders');
        $this->filesTable = new DataController('Files');
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SearchController();
        }

        return self::$instance;
    }

    public function queryDb($search)
    {
        $result = [];
        $files = $this->filesTable->searchBYName($search);
        $subfolder = $this->subfoldersTable->searchBYName($search);
        $query = array_merge($subfolder, $files);
        if (count($query) > 0) {
            foreach ($query as $file) {
                $subfiles = [];
                $folder_id = $file['folder_id'];
                $subfiles = $this->collection($subfiles, $folder_id);
                $subfiles = array_reverse($subfiles);
                $name = $file['name'];
                $subfiles[] = $name;
                $list = implode(', ', $subfiles);
                $string = str_replace(", ", "/", $list);
                $result[] = $string;
            }
        }
        if (count($result) < 1) {
            return false;
        }
        return $result;
    }
    private function collection($subfiles, $folder_id = null)
    {
        if ($folder_id == null) {
            return $subfiles;
        } else {
            $sub_folder = $this->subfoldersTable->FindOne($folder_id);
            $folder_id = $sub_folder['folder_id'];
            $position = $sub_folder['position'];
            $subfiles[] = $sub_folder['name'];
            if ($position > 1) {
                return $this->collection($subfiles, $folder_id);
            } else if ($position == 1) {
                $folder = $this->foldersTable->FindOne($folder_id);
                $subfiles[] = $folder['name'];
                return $this->collection($subfiles, null);
            }
        }

    }

}