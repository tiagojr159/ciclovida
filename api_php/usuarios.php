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
        $stmt = $pdo->query("SELECT * FROM usuario");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($usuarios);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["mensagem" => "Erro ao buscar usuários: " . $e->getMessage()]);
    }

} elseif ($metodo === 'POST') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($dados['nome']) && isset($dados['tipo']) && isset($dados['email']) && isset($dados['senha'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuario (nome, tipo, endereco, telefone, documento, senha, cep, email) VALUES (:nome, :tipo, :endereco, :telefone, :documento, :senha, :cep, :email)");
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':tipo', $dados['tipo']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':documento', $dados['documento']);
            $stmt->bindParam(':senha', $dados['senha']);
            $stmt->bindParam(':cep', $dados['cep']);
            $stmt->bindParam(':email', $dados['email']);

            $stmt->execute();
            $result->debugDumpParams();
            // Retornar resposta de sucesso
            echo json_encode([
                "mensagem" => "Usuário cadastrado com sucesso!",
                "usuario" => $dados
            ]);
        } catch (PDOException $e) {
            $result->debugDumpParams();
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao cadastrar usuário: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["mensagem" => "Dados inválidos! Nome, tipo, email e senha são obrigatórios."]);
    }

} elseif ($metodo === 'PUT') {
    include 'conexao.php'; // Inclui a conexão ao banco de dados

    $dados = json_decode(file_get_contents("php://input"), true);

    if ($dados && isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        try {
            $stmt = $pdo->prepare("UPDATE usuario SET nome = :nome, tipo = :tipo, endereco = :endereco, telefone = :telefone, documento = :documento, senha = :senha, cep = :cep, email = :email WHERE id = :id");
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':tipo', $dados['tipo']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':documento', $dados['documento']);
            $stmt->bindParam(':senha', $dados['senha']);
            $stmt->bindParam(':cep', $dados['cep']);
            $stmt->bindParam(':email', $dados['email']);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "mensagem" => "Usuário {$id} atualizado com sucesso!",
                    "usuarioAtualizado" => $dados
                ]);
            } else {
                echo json_encode([
                    "mensagem" => "Nenhuma alteração feita no usuário {$id}."
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao atualizar usuário: " . $e->getMessage()]);
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
            $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "mensagem" => "Usuário {$id} excluído com sucesso!"
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Usuário {$id} não encontrado ou já excluído."
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensagem" => "Erro ao excluir usuário: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["mensagem" => "ID não informado!"]);
    }
}
?>