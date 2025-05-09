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

        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM ponto_coleta WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
            $stmt->execute();
            $ponto = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($ponto ?: []);
        } elseif (isset($_GET['id_user'])) {
            $stmt = $pdo->prepare("SELECT 
                                          id AS ponto_id,
                                nome_ponto AS nome_ponto,
                                endereco AS endereco,
                                descricao AS descricao,
                                responsavel AS responsavel,
                                telefone AS telefone,
                                log,
                                lat,
                                id_user AS id_user,
                                tipo AS tipo,
                                (select solicitacao.status from solicitacao where id_ponto = ponto_coleta.id order by solicitacao.id desc limit 1 ) as status
                            FROM ponto_coleta
                            where ponto_coleta.id_user = :id_user");
            $stmt->bindParam(':id_user', $_GET['id_user'], PDO::PARAM_INT);
            $stmt->execute();
            $pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pontos);
        } else {
            $stmt = $pdo->query("SELECT 
                               
                                id AS ponto_id,
                                nome_ponto AS ponto_nome_ponto,
                                endereco AS ponto_endereco,
                                descricao AS ponto_descricao,
                                responsavel AS ponto_responsavel,
                                telefone AS ponto_telefone,
                                log,
                                lat,
                                id_user AS id_user,
                                tipo AS ponto_tipo,
                                (select solicitacao.status from solicitacao where id_ponto = ponto_coleta.id limit 1 ) as status
                            FROM ponto_coleta");
            $pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pontos);
        }
    

} elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!$dados) {
        http_response_code(400);
        echo json_encode(["erro" => "JSON inválido."]);
        exit;
    }

    // Atualização
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

            echo json_encode(["mensagem" => "Ponto de coleta atualizado com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar ponto: " . $e->getMessage()]);
        }

        // Cadastro com id_user
    } elseif (isset($dados['nome'], $dados['telefone'], $dados['endereco'], $dados['id_user'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ponto_coleta 
                (nome_ponto, telefone, responsavel, endereco, lat, log, id_user) 
                VALUES (:nome, :telefone, :responsavel, :endereco, :latitude, :longitude, :id_user)");

            $stmt->execute([
                ':nome' => $dados['nome'],
                ':telefone' => $dados['telefone'],
                ':responsavel' => $dados['responsavel'],
                ':endereco' => $dados['endereco'],
                ':latitude' => $dados['latitude'],
                ':longitude' => $dados['longitude'],
                ':id_user' => $dados['id_user']
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
            $stmt = $pdo->prepare("UPDATE ponto_coleta SET 
                nome_ponto = :nome_ponto, 
                endereco = :endereco, 
                descricao = :descricao, 
                responsavel = :responsavel, 
                contato = :contato 
                WHERE id = :id");

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