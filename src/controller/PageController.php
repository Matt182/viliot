<?php
namespace viliot\controller;

use viliot\utils\Helper;

class PageController {

    private $registry;

    public function __construct($registry) {
        $this->registry = $registry;
    }

    public function allTopics() {
        $topics = $this->registry->get('topicModel')->getAll();
        return [
            'template' => 'main',
            'topics' => $topics,
            'result' => 'success'
        ];
    }

    public function showTopic() {
        $args = $this->registry->get('request')->getArgs();
        if(!Helper::isValid($args, ['id'])) {
            throw new \Exception("Error Processing Request", 1);
        }
        $topic = $this->registry->get('topicModel')->get($args['id']);
        $comments = $this->registry->get('commentModel')->getAll($topic['id']);
        return [
            'template' => 'topic',
            'topic' => $topic,
            'comments' => $comments,
            'result' => 'success'
        ];
    }

    public function createComment() {
        $args = $this->registry->get('request')->getArgs();
        if(!Helper::isValid($args, ['topicId', 'parentId', 'body'])) {
            throw new \Exception("Error Processing Request", 1);
        }
        $id = $this->registry->get('commentModel')->create($args);
        $comment = $this->registry->get('commentModel')->get($id);
        return [
            'template' => 'comment',
            'comment' => $comment,
            'result' => 'success'
        ];
    }

    public function deleteComment() {
        $args = $this->registry->get('request')->getArgs();
        if(!Helper::isValid($args, ['topicId', 'id'])) {
            throw new \Exception("Error Processing Request", 1);
        }
        $this->registry->get('commentModel')->delete($args);
        return ['result' => 'success'];
    }
}
