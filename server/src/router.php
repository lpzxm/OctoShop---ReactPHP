<?php
require_once 'database.php';

use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

function handleRequest(ServerRequestInterface $request)
{
    $uri = $request->getUri()->getPath();
    $method = $request->getMethod();


    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg)$/', $uri)) {
        return serveStaticFile($uri);
    }

    //inicio
    if ($uri === "/") {
        return serveFile(__DIR__ . "/../public/index.html", "text/html");
    }

    //contacto
    if ($uri === "/contact") {
        return serveFile(__DIR__ . "/../public/contact.html", "text/html");
    }

    if ($uri === "/style") {
        return serveFile(__DIR__ . "/../public/style.css", "text/plain");
    }

    //datos en json
    if ($uri === "/data") {
        if ($method === "GET") {
            return getContacts();
        } elseif ($method === "POST") {
            return createContact($request);
        }
    }

    //manejar contactos
    if (preg_match('/\/data\/(\d+)/', $uri, $matches)) {
        $contactId = $matches[1];
        if ($method === "PUT") {
            return updateContact($request, $contactId);
        } elseif ($method === "DELETE") {
            return deleteContact($contactId);
        }
    }

    return new Response(404, ['Content-Type' => 'text/plain'], "Página no encontrada");
}


function serveFile($filePath, $contentType)
{
    if (!file_exists($filePath)) {
        return new Response(404, ['Content-Type' => 'text/plain'], "Archivo no encontrado");
    }

    $content = file_get_contents($filePath);
    return new Response(200, ['Content-Type' => $contentType], $content);
}

// servir archivos estáticos
function serveStaticFile($uri)
{
    $filePath = __DIR__ . "/../public" . $uri;
    if (!file_exists($filePath) || !is_file($filePath)) {
        return new Response(404, ['Content-Type' => 'text/plain'], "Archivo no encontrado");
    }

    $mimeType = getMimeType($filePath);
    return new Response(200, ['Content-Type' => $mimeType], file_get_contents($filePath));
}

//
function getMimeType($filePath)
{
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml'
    ];
    return $mimeTypes[$ext] ?? 'application/octet-stream';
}
