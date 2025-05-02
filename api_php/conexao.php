<?php
$host = 'localhost';
$db = 'cidadao13';
$user = 'root';
$pass = '';



$host = 'localhost'; // servidor
$db = 'ki6com20_ciclovida'; // usuario do banco
$pass = 'igarassu12!@'; // senha do usuario do banco
$user = 'ki6com20_paixao'; // nome do banco


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Configura o PDO para lançar exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Tudo certo!
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}
?>
