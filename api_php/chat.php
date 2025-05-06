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

$apiKey = '';

$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "Você é um assistente virtual que só pode falar sobre os temas: Integração logística para compartilhar alimentos, Doação segura, Destinação correta, Política pública, Engajamento comunitário de Recife. Evite qualquer outro assunto., escreva sempre textos muitos curtos e simples de entender"],
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

if (isset($result["choices"][0]["message"]["content"])) {
    $reply = $result["choices"][0]["message"]["content"];
} else {
    http_response_code(500);
    $reply = "Erro: A resposta da API não pôde ser interpretada. Verifique se a chave da API está correta e se o servidor da OpenAI está respondendo corretamente.";
}
echo json_encode(["reply" => $reply]);
