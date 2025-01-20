<?php
// Clave secreta
$secret_key = "TU_SECRETO";

// Información del usuario (payload)
$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
$payload = json_encode([
    'user_id' => $user_id,
    'email' => $user_email,
    'iat' => time(),
    'exp' => time() + (60*60) // Tiempo de expiración (1 hora)
]);

// Codifica en Base64Url
$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

// Firma del token
$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret_key, true);
$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

// Genera el JWT
$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

echo json_encode([
    "success" => 1,
    "token" => $jwt
]);
?>
