<?php
// Permitir CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Conexão
include 'conexao.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM ponto_coleta WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
            $stmt->execute();
            $ponto = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($ponto ?: []);
        } else {
            $stmt = $pdo->query("SELECT * FROM ponto_coleta");
            $pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pontos);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao buscar dados: " . $e->getMessage()]);
    }

}  elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!$dados) {
        http_response_code(400);
        echo json_encode(["erro" => "JSON inválido."]);
        exit;
    }

    // Atualização se vier com 'id_original'
    if (isset($dados['id_original'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ponto_coleta SET 
                nome_ponto = :nome,
                telefone = :telefone,
                responsavel = :responsavel,
                endereco = :endereco,
                lat = :latitude,
                log = :longitude
                WHERE id = :id_original");

            $stmt->execute([
                ':nome' => $dados['nome'],
                ':telefone' => $dados['telefone'],
                ':responsavel' => $dados['responsavel'],
                ':endereco' => $dados['endereco'],
                ':latitude' => $dados['latitude'],
                ':longitude' => $dados['longitude'],
                ':id_original' => $dados['id_original']
            ]);

            echo json_encode(["mensagem" => "Ponto atualizado com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar ponto: " . $e->getMessage()]);
        }

    // Cadastro se não houver id_original
    } elseif (isset($dados['nome'], $dados['telefone'], $dados['endereco'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ponto_coleta 
                (nome_ponto, telefone, responsavel, endereco, lat, log) 
                VALUES (:nome, :telefone, :responsavel, :endereco, :latitude, :longitude)");

            $stmt->execute([
                ':nome' => $dados['nome'],
                ':telefone' => $dados['telefone'],
                ':responsavel' => $dados['responsavel'],
                ':endereco' => $dados['endereco'],
                ':latitude' => $dados['latitude'],
                ':longitude' => $dados['longitude']
            ]);

            echo json_encode(["mensagem" => "Ponto de coleta cadastrado com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar ponto: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "Campos obrigatórios ausentes."]);
    }
} elseif ($metodo === 'PUT') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ponto_coleta SET nome_ponto = :nome_ponto, endereco = :endereco, descricao = :descricao, responsavel = :responsavel, contato = :contato WHERE id = :id");
            $stmt->execute([
                ':nome_ponto' => $dados['nome_ponto'] ?? '',
                ':endereco' => $dados['endereco'] ?? '',
                ':descricao' => $dados['descricao'] ?? null,
                ':responsavel' => $dados['responsavel'] ?? null,
                ':contato' => $dados['contato'] ?? null,
                ':id' => $_GET['id']
            ]);

            echo json_encode(["mensagem" => "Ponto de coleta atualizado com sucesso."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "ID e dados obrigatórios."]);
    }

} elseif ($metodo === 'DELETE') {
    if (isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM ponto_coleta WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();

            echo json_encode(["mensagem" => "Ponto de coleta excluído com sucesso."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "ID não informado."]);
    }
}
?>
