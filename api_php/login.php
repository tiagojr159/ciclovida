<?php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

include 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !isset($dados['documento']) || !isset($dados['senha'])) {
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados inválidos!"]);
    exit;
}

// Remove pontos, traços e barras do documento
$documento = preg_replace('/[\.\-\/]/', '', $dados['documento']);
$senha = $dados['senha'];

try {
    $stmt = $pdo->prepare("SELECT id, nome, documento, senha, agente FROM usuario WHERE documento = :documento");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $usuario['senha'] === $senha) {
        echo json_encode([
            "mensagem" => "Login realizado com sucesso!",
            "usuario" => [
                "nome" => $usuario['nome'],
                "id_user" => $usuario['id'],
                "agente" => $usuario['agente'],
                "documento" => $usuario['documento']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["mensagem" => "Documento ou senha incorretos."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["mensagem" => "Erro no servidor: " . $e->getMessage()]);
}
?>
