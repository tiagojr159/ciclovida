<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Content-Type: application/json');

$metodo = $_SERVER['REQUEST_METHOD'];

// Corrigir método PUT e DELETE
if ($metodo === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $metodo = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
}

if ($metodo === 'GET') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    try {
        $stmt = $pdo->query("SELECT * FROM empresas");
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($empresas);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["mensagem" => "Erro ao buscar empresas: " . $e->getMessage()]);
    }

} elseif ($metodo === 'POST') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($dados['nome']) && isset($dados['tipo'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO empresas (nome, tipo, endereco, telefone) VALUES (:nome, :tipo, :endereco, :telefone)");
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':tipo', $dados['tipo']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);

            $stmt->execute();

            // Retornar resposta de sucesso
            echo json_encode([
                "mensagem" => "Empresa cadastrada com sucesso!",
                "empresa" => $dados
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao cadastrar empresa: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["mensagem" => "Dados inválidos! Nome e tipo são obrigatórios."]);
    }

} elseif ($metodo === 'PUT') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        try {
            $stmt = $pdo->prepare("UPDATE empresas SET nome = :nome, tipo = :tipo, endereco = :endereco, telefone = :telefone WHERE id = :id");
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':tipo', $dados['tipo']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "mensagem" => "Empresa {$id} atualizada com sucesso!",
                    "empresaAtualizada" => $dados
                ]);
            } else {
                echo json_encode([
                    "mensagem" => "Nenhuma alteração feita na empresa {$id}."
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao atualizar empresa: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["mensagem" => "ID ou dados inválidos!"]);
    }

} elseif ($metodo === 'DELETE') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM empresas WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "mensagem" => "Empresa {$id} excluída com sucesso!"
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Empresa {$id} não encontrada ou já excluída."
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao excluir empresa: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["mensagem" => "ID não informado!"]);
    }
}
?>
