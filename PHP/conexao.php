<?php 

$host = "localhost";
$db   = "novatendencia";
$user = "root";
$pass = "";


//estabelecendo
try{
    $pdo = new PDO ("mysql:host=$host;dbname=$db;
    charset=utf8mb4", $user, $pass);
    //verificando se deu certo ou não 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //IMPRIMINDO MENSAGEM CASO TENHA DADO CERTO
    //echo "Conexão bem-sucedida!"; // (opcional para teste)

} catch (PDOExption $e) {
    // caso dê erro ele executa o catch e imprime a mensagem 
    die("Erro ao conectar ao banco de dados: "
    . $e->getMessage());
}


?>