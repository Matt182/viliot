<?php
namespace viliot\model;

class CommentModel {

    private $db;
    private static $instance = null;

    private function __construct()
    {
        $this->db = DB::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new CommentModel();
        }
        return self::$instance;
    }

    public function get($id) {
        $comment = $this->db->getById($id, 'comments');
        if (!$comment) {
            throw new \Exception("comment with id=$id doesnt exists", 1);
        }
        return $comment;
    }

    public function create($args) {
        $topicId = $args['topicId'];
        $parentId = $args['parentId'];
        $body = $args['body'];
        $id = $this->db->createComment($topicId, $parentId, $body);
        return $id;
    }

    public function delete($args) {
        $id = $args['id'];
        $topicId = $args['topicId'];
        $this->db->deleteComment($id, $topicId);
    }

    public function getAll($topicId) {
        $comments = $this->db->getComments($topicId);
        $tree = $this->buildTree($comments);
        return $tree;
    }

    private function buildTree(&$comments, $parentId = 0) {
        $branch = [];
        foreach ($comments as &$comment) {
            if ($comment['parent_id'] == $parentId) {
                $children = $this->buildTree($comments, $comment['id']);
                if ($children) {
                    $comment['children'] = $children;
                }
                $branch[$comment['id']] = $comment;
                unset($comment);
            }
        }
        return $branch;
    }
}
