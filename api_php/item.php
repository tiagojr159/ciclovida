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

include 'conexao.php'; // conexão PDO com o banco

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM itens WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($item ?: []);
        } elseif (isset($_GET['id_user'])) {
            $stmt = $pdo->prepare("SELECT * FROM itens WHERE id_user = :id_user");
            $stmt->bindParam(':id_user', $_GET['id_user'], PDO::PARAM_INT);
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($itens);
        } else {
            $stmt = $pdo->query("SELECT * FROM itens");
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($itens);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao buscar itens: " . $e->getMessage()]);
    }

} elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!$dados) {
        http_response_code(400);
        echo json_encode(["erro" => "JSON inválido."]);
        exit;
    }

    // Novo endpoint para solicitação
    if (isset($dados['acao']) && $dados['acao'] === 'solicitar') {
        if (isset($dados['id_user'], $dados['id_item'], $dados['id_ponto'])) {
            try {
                // Verificar se o item existe e tem quantidade disponível
                if ($dados['id_item'] == 0) {
                    $stmt = $pdo->prepare("UPDATE solicitacao SET status = 0 WHERE id_user = :id_user and id_ponto = :id_ponto");
                    $stmt->bindParam(':id_user', $dados['id_user'], PDO::PARAM_INT);
                    $stmt->bindParam(':id_ponto', $dados['id_ponto'], PDO::PARAM_INT);
                    $stmt->execute();
                }


                // Inserir solicitação
                $stmt = $pdo->prepare("INSERT INTO solicitacao (id_user, id_item, id_ponto, status) VALUES (:id_user, :id_item, :id_ponto, :status)");
                $stmt->execute([
                    ':id_user' => $dados['id_user'],
                    ':id_item' => $dados['id_item'],
                    ':id_ponto' => $dados['id_ponto'],
                    ':status' => $dados['status']
                ]);


                echo json_encode(["mensagem" => "Alimento solicitado com sucesso à secretaria SEAU!"]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["erro" => "Erro ao registrar solicitação: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Parâmetros ausentes: id_user, id_produto ou id_ponto."]);
        }

        // Atualização de item se vier com 'id_original'
    } elseif (isset($dados['id_original'])) {
        try {
            $stmt = $pdo->prepare("UPDATE itens SET 
                nome = :nome, 
                id_ponto = :id_ponto, 
                quantidade = :quantidade, 
                id_user = :id_user, 
                descricao = :descricao, 
                valor = :valor 
                WHERE id = :id_original");

            $stmt->execute([
                ':nome' => $dados['nome'],
                ':id_ponto' => $dados['id_ponto'],
                ':quantidade' => $dados['quantidade'],
                ':id_user' => $dados['id_user'],
                ':descricao' => $dados['descricao'],
                ':valor' => $dados['valor'],
                ':id_original' => $dados['id_original']
            ]);

            echo json_encode(["mensagem" => "Item atualizado com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar item: " . $e->getMessage()]);
        }

        // Cadastro de novo item
    } elseif (isset($dados['nome'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO itens 
                (nome, id_ponto, quantidade, id_user, descricao, valor) 
                VALUES (:nome, :id_ponto, :quantidade, :id_user, :descricao, :valor)");

            $stmt->execute([
                ':nome' => $dados['nome'],
                ':id_ponto' => $dados['id_ponto'],
                ':quantidade' => $dados['quantidade'] ?? 0,
                ':id_user' => $dados['id_user'],
                ':descricao' => $dados['descricao'] ?? null,
                ':valor' => $dados['valor'] ?? 0.00
            ]);

            echo json_encode(["mensagem" => "Item cadastrado com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar item: " . $e->getMessage()]);
        }

        // Alteração de status
    } elseif (isset($dados['acao']) && $dados['acao'] === 'alterar_status') {
        if (isset($dados['id']) && isset($dados['status'])) {
            try {
                $stmt = $pdo->prepare("UPDATE itens SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $dados['status'], PDO::PARAM_INT);
                $stmt->bindParam(':id', $dados['id'], PDO::PARAM_INT);
                $stmt->execute();
                echo json_encode(["mensagem" => "Status atualizado com sucesso."]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["erro" => "Erro ao atualizar status: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Parâmetros ausentes para alteração de status."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "Campos obrigatórios: nome, id_ponto, id_user"]);
    }

} elseif ($metodo === 'PUT') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE itens SET nome = :nome, id_ponto = :id_ponto, quantidade = :quantidade, id_user = :id_user, descricao = :descricao, valor = :valor WHERE id = :id");
            $stmt->execute([
                ':nome' => $dados['nome'],
                ':id_ponto' => $dados['id_ponto'],
                ':quantidade' => $dados['quantidade'] ?? 0,
                ':id_user' => $dados['id_user'],
                ':descricao' => $dados['descricao'] ?? null,
                ':valor' => $dados['valor'] ?? 0.00,
                ':id' => $_GET['(Max id']
            ]);

            echo json_encode(["mensagem" => "Item atualizado com sucesso."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar item: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "ID ou dados inválidos."]);
    }

} elseif ($metodo === 'DELETE') {
    if (isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM itens WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();

            echo json_encode(["mensagem" => "Item excluído com sucesso."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir item: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "ID não informado."]);
    }
}
?>