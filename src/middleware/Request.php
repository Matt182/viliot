<?php
namespace viliot\middleware;

use viliot\utils\Helper;

/**
 * Request handler
 */
class Request
{
    private $args;
    private $url;

    public function __construct()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $this->url = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $args = [];
        if ($method == 'POST') {
            $args = $_POST;
        }
        if ($method == 'GET') {
            $args = $_GET;
        }
        $this->args = Helper::clearEmpty($args);
    }

    public function getUrl() {
        return $this->url;
    }

    public function getArgs() {
        return $this->args;
    }
}
