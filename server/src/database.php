<?php

use React\Promise\Promise;
use React\Http\Message\Response;

class Database
{
    private static $pdo = null;

    public static function getInstance()
    {
        if (!self::$pdo) {
            self::$pdo = new PDO("mysql:host=localhost;dbname=productos_db;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }
        return self::$pdo;
    }
}

// todos los contactos (GET /data)
function getContacts()
{
    return new Promise(function ($resolve) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM contacts");
        $data = $stmt->fetchAll();

        $resolve(new Response(200, ['Content-Type' => 'application/json'], json_encode($data)));
    });
}

function createContact($request)
{
    return new Promise(function ($resolve) use ($request) {
        $body = json_decode($request->getBody()->getContents(), true); // Obtener el JSON del cuerpo

        if (!$body || !isset($body['name']) || !isset($body['email']) || !isset($body['message'])) {
            return $resolve(new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'Faltan datos'])));
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$body['name'], $body['email'], $body['message']]);

            return $resolve(new Response(201, ['Content-Type' => 'application/json'], json_encode(['message' => 'Contacto agregado'])));
        } catch (Exception $e) {
            error_log("Error en la base de datos: " . $e->getMessage());
            return $resolve(new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => $e->getMessage()])));
        }
    });
}

// edit contacto (PUT /data/{id})
function updateContact($request, $id)
{
    return new Promise(function ($resolve, $reject) use ($request, $id) {
        $db = Database::getInstance();
        $body = json_decode($request->getBody()->getContents(), true);

        if (!isset($body['name']) || !isset($body['email']) || !isset($body['message'])) {
            return $resolve(new Response(400, ['Content-Type' => 'text/plain'], "Faltan datos"));
        }

        $stmt = $db->prepare("UPDATE contacts SET name = ?, email = ?, message = ? WHERE id = ?");
        $stmt->execute([$body['name'], $body['email'], $body['message'], $id]);

        $resolve(new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Contacto actualizado'])));
    });
}

// delete contacto (DELETE /data/{id})
function deleteContact($id)
{
    return new Promise(function ($resolve, $reject) use ($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);

        $resolve(new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Contacto eliminado'])));
    });
}
