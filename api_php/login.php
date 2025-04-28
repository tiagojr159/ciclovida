<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Content-Type: application/json');

include 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !isset($dados['documento']) || !isset($dados['senha'])) {
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados inválidos!"]);
    exit;
}

$documento = $dados['documento'];
$senha = $dados['senha'];

try {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE documento = :documento");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $usuario['senha'] === $senha) {
        echo json_encode(["mensagem" => "Login realizado com sucesso!", "usuario" => ["documento" => $documento]]);
    } else {
        http_response_code(401);
        echo json_encode(["mensagem" => "Documento ou senha incorretos22."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["mensagem" => "Erro no servidor: " . $e->getMessage()]);
}
?>
