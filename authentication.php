<?php

// Affichage des erreurs sur Hostinger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'jwt_utils.php';
require_once 'connexionDB.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$secret = 'your-256-bit-secret';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Endpoint pour obtenir le token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    $login = isset($input['login']) ? trim($input['login']) : null;
    $password = isset($input['password']) ? trim($input['password']) : null;

    if ($login && $password) {
        $stmt = $linkpdo->prepare("SELECT * FROM user WHERE login = :login");
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                $headers = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];

                $payload = [
                    'sub' => $login,
                    'exp' => time() + 86400,
                    'role' => $user['role']
                ];

                $jwt = generate_jwt($headers, $payload, $secret);

                // Renvoyer la réponse formatée
                echo json_encode([
                    "status" => "success",
                    "status_code" => 200,
                    "status_message" => "[Drafteam API] : Authentification OK",
                    "data" => $jwt
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    "status" => "error",
                    "status_code" => 401,
                    "status_message" => "[Drafteam API] : Identifiants invalides"
                ]);
            }
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "status_code" => 401,
                "status_message" => "[Drafteam API] : Le login et le mot de passe sont requis"
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "status_code" => 400,
            "status_message" => "[Drafteam API] : Donnees manquantes"
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Endpoint pour vérifier le token
    $token = isset($_GET['token']) ? $_GET['token'] : null;

    if ($token) {
        if (is_jwt_valid($token, $secret)) {
            echo json_encode([
                "status" => "success",
                "status_code" => 200,
                "status_message" => "[Drafteam API] : Token valide"
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "status_code" => 401,
                "status_message" => "[Drafteam API] : Token invalide"
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "status_code" => 400,
            "status_message" => "[Drafteam API] : Token manquant"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Methode non autorisee, utilisez POST ou GET.']);
    exit;
}

?>
