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
    $statement = $pdo->prepare('SELECT * from animal where id > ? and deleted_at = 0 order by id limit 10');
    $statement->execute([$id,]);

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $response->withJson($result);
});

$app->get('/', function ($request, $response, $args) {
    return $response->withStatus(200)
        ->withHeader('Content-type', 'text/html')
        ->withBody(new Stream(fopen("index.html", "r")));
});

$app->get('/animals/{animal_id}', function ($request, $response, $args) {
    $animal_id = $request->getAttribute('animal_id');
    $pdo = new PDO(getenv('MYSQL_DSN'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
    $statement = $pdo->prepare('SELECT * FROM animal WHERE animal_id = ?');
    $statement->execute([$animal_id,]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        return $response->withStatus(404);
    }

    $format = $request->getQueryParam('format', 'html');
    if ($format === 'json') {
        return $response->withJson($result);
    }

    $loader = new Twig_Loader_Filesystem(__DIR__ . '/template');
    $twig = new Twig_Environment($loader);
    $response->getBody()->write($twig->load('animal.html')->render(['animal' => $result,]));

    return $response->withHeader('Content-type', 'text/html');
});

$app->run();
