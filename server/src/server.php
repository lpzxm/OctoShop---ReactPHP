<?php
require '../../vendor/autoload.php';
require_once 'router.php';

use React\Http\HttpServer;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;


$loop = React\EventLoop\Loop::get();

// crear el servidor
$server = new HttpServer(
    function (ServerRequestInterface $request) {
        return handleRequest($request);
    }
);

// socket en el puerto 8080
$socket = new SocketServer('127.0.0.1:8080', [], $loop);
$server->listen($socket);

echo "Servidor corriendo en http://127.0.0.1:8080\n";

//correr el servidor hasta que se desee cerrar
$loop->run();
