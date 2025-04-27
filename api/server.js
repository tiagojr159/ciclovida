// Importando as bibliotecas
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Permitir requisições de qualquer origem
app.use(cors());

// Rota para buscar usuários
app.get('/usuarios', (req, res) => {
    const usuarios = [
        { id: 1, nome: 'João Silva', email: 'joao@example.com' },
        { id: 2, nome: 'Maria Oliveira', email: 'maria@example.com' },
        { id: 3, nome: 'Pedro Souza', email: 'pedro@example.com' }
    ];
    res.json(usuarios);
});

app.use(express.json());

// Rota POST para cadastrar usuário
app.post('/usuarios', (req, res) => {
    const novoUsuario = req.body;

    // Aqui você poderia salvar no banco de dados
    console.log('Usuário recebido:', novoUsuario);

    res.status(201).json({
        mensagem: 'Usuário criado com sucesso111!',
        usuario: novoUsuario
    });
});

app.put('/usuarios/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const novosDados = req.body;

    // Aqui você faria a lógica para atualizar no banco de dados
    console.log(`Atualizar usuário com id ${id}:`, novosDados);

    res.json({
        mensagem: `Usuário ${id} atualizado com sucesso.`,
        usuarioAtualizado: novosDados
    });
});

// Rota DELETE para apagar usuário
app.delete('/usuarios/:id', (req, res) => {
    const id = parseInt(req.params.id);

    // Aqui você faria a lógica para apagar do banco de dados
    console.log(`Excluir usuário com id ${id}`);

    res.json({
        mensagem: `Usuário ${id} excluído com sucesso.`
    });
});




// Iniciar o servidor
app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT}`);
});
