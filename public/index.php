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
    $result = [];
    foreach ($pdo->query("SELECT * from animal where id > " . intval($id) . " order by id limit 10") as $row) {
        $result[] = [
            'id' => $row['id'],
            'animal_place' => $row['animal_place'],
            'animal_sex' => $row['animal_sex'],
            'album_file' => $row['album_file'],
            'animal_remark' => $row['animal_remark'],
        ];
    }

    $response = $response->withStatus(200)
        ->withHeader('Access-Control-Allow-Origin', "*")
        ->withJson($result);

    return $response;
});

$app->get('/', function ($request, $response, $args) {
    return $response->withStatus(200)
        ->withHeader('Content-type', 'text/html')
        ->withBody(new Stream(fopen("index.html", "r")));
});

$app->run();
