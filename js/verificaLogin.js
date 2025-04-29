window.onload = function () {
    verificarLogin();
};

function verificarLogin() {
    const usuarioLogado = localStorage.getItem('usuarioLogado');
    console.log('logado: ' + usuarioLogado)
    if (!usuarioLogado) {
        alert('Você precisa estar logado para acessar esta página.');
        window.location.href = 'login.html'; // Redireciona para o login
    }
}

function logout() {
    localStorage.removeItem('usuarioLogado');
    window.location.href = 'login.html';
}