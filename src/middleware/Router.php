<?php
namespace viliot\middleware;

/**
 * Router
 */
class Router {

    private $routes;

    private $method;

    private $request;

    public function __construct($request) {
        $this->routes = [
            '' => [
                'method' => 'allTopics'
            ],
            'topic' => [
                'method' => 'showTopic'
            ],
            'topic/createComment' => [
                'method' => 'createComment'
            ],
            'topic/deleteComment' => [
                'method' => 'deleteComment'
            ]
        ];
        $this->request = $request;
        if (!isset($this->routes[$this->request->getUrl()])) {
            header('Location:/');
        }

        $this->method = $this->routes[$this->request->getUrl()]['method'];

    }

    public function getMethod() {
        return $this->method;
    }

}
