<?php
namespace viliot\model;

use viliot\model\DB;

class TopicModel {

    private $db;
    private static $instance = null;

    private function __construct()
    {
        $this->db = DB::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new TopicModel();
        }
        return self::$instance;
    }


    public function get($id) {
        $topic = $this->db->getById($id, 'topics');
        if (!$topic) {
            throw new \Exception("topic with id=$id doesnt exists", 1);
        }
        return $topic;
    }

    public function getAll() {
        $topics = $this->db->getTopics();
        return $topics;
    }
}
 ?>
