<?php
namespace viliot;

use viliot\middleware\Registry;
use viliot\middleware\Request;
use viliot\middleware\Router;
use viliot\controller\PageController;
use viliot\model\CommentModel;
use viliot\model\TopicModel;
use viliot\model\DB;
use viliot\view\Viewer;

require_once 'vendor/autoload.php';
require_once 'config.php';


try {
    $registry = new Registry();
    $registry->set('request', new Request());

    $router = new Router($registry->get('request'));

    $registry->set('topicModel', TopicModel::getInstance());
    $registry->set('commentModel', CommentModel::getInstance());
    $registry->set('db', DB::getInstance());


    $controller = new PageController($registry);

    $response = $controller->{$router->getMethod()}();
    print(Viewer::render($response));
} catch (\Exception $e) {
    print($e->getMessage());
}

?>
