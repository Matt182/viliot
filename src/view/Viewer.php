<?php
namespace viliot\view;

class Viewer {

    public static $count = 0;
    public static function render($response) {
        if (!isset($response['template'])) {
            return $response['result'];
        }
        $page = $response['template'];
        ob_start();
        include_once "html/$page.html";
        return ob_get_clean();
    }

    public static function renderCommentsTree($tree = []) {

        foreach ($tree as $comment) {
            include 'html/comment.html';
        }
    }
}
