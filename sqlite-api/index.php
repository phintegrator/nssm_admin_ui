<?php
// index.php

// CORS HEADERS
header("Access-Control-Allow-Origin: *"); // You can restrict it to specific domains if needed
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT * FROM servers WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $db->query("SELECT * FROM servers");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $stmt = $db->prepare("INSERT INTO servers (name, ip, description) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $input['ip'], $input['description']]);
        echo json_encode(["message" => "Server created", "id" => $db->lastInsertId()]);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing ID for update"]);
            break;
        }
        $stmt = $db->prepare("UPDATE servers SET name = ?, ip = ?, description = ? WHERE id = ?");
        $stmt->execute([$input['name'], $input['ip'], $input['description'], $_GET['id']]);
        echo json_encode(["message" => "Server updated"]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing ID for delete"]);
            break;
        }
        $stmt = $db->prepare("DELETE FROM servers WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(["message" => "Server deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
