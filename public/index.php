<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . "/../vendor/autoload.php";

use Slim\App as App;
use Slim\Http\Stream;

$app = new App();

$app->get('/animals', function ($request, $response, $args) {
    $id = $request->getQueryParam("id", 0);
    $pdo = new PDO(getenv('MYSQL_DSN'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
    $statement = $pdo->prepare('SELECT * from animal where id > ? order by id limit 10');
    $statement->execute([$id,]);

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $response->withJson($result);
});

$app->get('/', function ($request, $response, $args) {
    return $response->withStatus(200)
        ->withHeader('Content-type', 'text/html')
        ->withBody(new Stream(fopen("index.html", "r")));
});

$app->run();
