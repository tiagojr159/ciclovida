<?php 
// Permitir requisições CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método não permitido
    echo json_encode(["error" => "Método não permitido. Use POST."]);
    exit;
}

// Recebe e valida o JSON do frontend
$inputRaw = file_get_contents("php://input");
$input = json_decode($inputRaw, true);

if (!is_array($input) || !isset($input["message"])) {
    http_response_code(400);
    echo json_encode(["error" => "Mensagem inválida."]);
    exit;
}

$userMessage = trim($input["message"]);

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(["error" => "Mensagem não enviada."]);
    exit;
}

//$apiKey = '';

$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "user", "content" => $userMessage]
    ]
];

// Chamada para a API OpenAI
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Erro na requisição
if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Erro na comunicação: $error"]);
    exit;
}

// Resposta da OpenAI
$result = json_decode($response, true);

$reply = $result["choices"][0]["message"]["content"] ?? "Não foi possível gerar uma resposta.";

echo json_encode(["reply" => $reply]);
