<?php
namespace viliot\model;

use PDO;

class DB {

    private $connection;
    private static $instance = null;

    private function __construct()
    {
        $dsn = getenv('driver') . ":dbname=" . getenv('dbname') . ";host=" . getenv('host');
        $dbusername = getenv('username');
        $dbpassword = getenv('password');
        try{
            $this->connection = new PDO($dsn, $dbusername, $dbpassword, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        } catch(PDOException $e) {
            throw new \Exception($e->getMessage);
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public function getTopics() {
        $sql = "select * from topics";
        $stmt = $this->connection->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getComments($topicId) {
        $stmt = $this->connection->prepare("select * from comments where topic_id=:topic_id order by id desc");
        $stmt->execute([':topic_id' => $topicId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getById($id, $table) {
        $stmt = $this->connection->prepare("select * from $table where id=:id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function deleteComment($id, $topicId) {

        try {
            $this->connection->beginTransaction();
            $stp = $this->connection->prepare("select * from comments where id =:id");
            $stp->bindParam(':id', $id, PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error when deleting id: $id", 1);
            $deleting = $stp->fetch(PDO::FETCH_ASSOC);
            $deletingLeft = $deleting['lft'];
            $deletingRight = $deleting['rgt'];
            $width = $deletingRight - $deletingLeft + 1;
            $stp = $this->connection->prepare("delete from comments where lft >= :left and lft <= :right and topic_id=:topic_id");
            $stp->bindParam(':left', $deletingLeft, PDO::PARAM_INT);
            $stp->bindParam(':right', $deletingRight, PDO::PARAM_INT);
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);

            $stp->execute();
            if(!$stp) throw new Exception("Error when deleting id: $id", 1);
            $stp = $this->connection->prepare("update comments set rgt = rgt - :width where rgt > :right and topic_id=:topic_id");
            $stp->bindParam(':width', $width, PDO::PARAM_INT);
            $stp->bindParam(':right', $deletingRight, PDO::PARAM_INT);
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error when deleting id: $id", 1);
            $stp = $this->connection->prepare("update comments set lft = lft - :width where lft > :right and topic_id=:topic_id");
            $stp->bindParam(':width', $width, PDO::PARAM_INT);
            $stp->bindParam(':right', $deletingRight, PDO::PARAM_INT);
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error when deleting id: $id", 1);

            $stp = $this->connection->prepare("select count(id) as count from comments where parent_id =:id");
            $stp->bindParam(':id', $deleting['parent_id'], PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error when deleting id: $id", 1);
            if($stp->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
                $stp = $this->connection->prepare("update comments set has_child=0 where id =:id");
                $stp->bindParam(':id', $deleting['parent_id'], PDO::PARAM_INT);
                $stp->execute();
                if(!$stp) throw new Exception("Error when deleting id: $id", 1);
            }

            $this->connection->commit();

            return $stp;
        } catch (Exception $e) {
            //log Exception
            $this->connection->rollBack();
            return $stp;
        }
    }

    public function createComment($topicId, $parentId, $body) {
        try {
            $this->connection->beginTransaction();
            if ($parentId == 0) {
                $parentLeft = 0;
            } else {

                $stp = $this->connection->prepare('select * from comments where id =:id');
                $stp->bindParam(':id', $parentId, PDO::PARAM_INT);
                $stp->execute();
                if(!$stp) throw new Exception("Error inserting into DB", 1);


                $parentRow = $stp->fetch(PDO::FETCH_ASSOC);
                $parentLeft = $parentRow['lft'];

            }
            $stp = $this->connection->prepare('update comments set rgt = rgt + 2 where rgt > :parentLeft and topic_id=:topic_id');
            $stp->bindParam(':parentLeft', $parentLeft, PDO::PARAM_INT);
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error inserting into DB", 1);

            $stp = $this->connection->prepare('update comments set lft = lft + 2 where lft > :parentLeft and topic_id=:topic_id');
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
            $stp->bindParam(':parentLeft', $parentLeft, PDO::PARAM_INT);
            $stp->execute();
            if(!$stp) throw new Exception("Error inserting into DB", 1);

            $stp = $this->connection->prepare('insert into comments (topic_id, body, parent_id, lft, rgt) values (:topic_id, :body, :parent_id, :lft, :rgt)');
            $stp->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
            $stp->bindParam(':body', $body, PDO::PARAM_STR);
            $stp->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            $left = $parentLeft + 1;
            $stp->bindParam(':lft', $left, PDO::PARAM_INT);
            $right = $parentLeft + 2;
            $stp->bindParam(':rgt', $right, PDO::PARAM_INT);
            $stp->execute();

            if(!$stp) throw new Exception("Error inserting into DB", 1);
            $insertedId = $this->connection->lastInsertId();
            $stp = $this->connection->prepare("update comments set has_child='1' where id=:id");
            $stp->bindParam(':id', $parentId, PDO::PARAM_INT);
            $stp->execute();

            if(!$stp) throw new Exception("Error inserting into DB", 1);
            $this->connection->commit();
            return $insertedId;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception("Error Processing Request", 1);
        }
    }
}
