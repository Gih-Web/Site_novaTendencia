<?php
require_once __DIR__ . "/conexao.php";

// Função para redirecionar com parâmetros na URL
function redirectWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

try {
    // só permite POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirectWith("../PAGINAS/login.html", ["erro" => "Método inválido"]);
    }

    // captura os dados do formulário
    $email = $_POST["email"] ?? "";
    $senha = $_POST["senha"] ?? "";

    // validação básica
    if ($email === "" || $senha === "") {
        redirectWith("../PAGINAS/login.html", ["erro" => "Preencha todos os campos"]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWith("../PAGINAS/login.html", ["erro" => "E-mail inválido"]);
    }

    // verifica se o email existe no banco
    $stmt = $pdo->prepare("SELECT * FROM Cliente WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        redirectWith("../PAGINAS/login.html", ["erro" => "E-mail não encontrado"]);
    }

    // compara senha (⚠️ ainda em texto puro, não seguro)
    if ($usuario["senha"] !== $senha) {
        redirectWith("../PAGINAS/login.html", ["erro" => "Senha incorreta"]);
    }

    // login bem-sucedido → cria sessão
    session_start();
    $_SESSION["usuario_id"] = $usuario["id"];   // supondo que sua tabela tenha "id"
    $_SESSION["usuario_nome"] = $usuario["nome"];

    // redireciona para a index
    redirectWith("../PAGINAS/index.html", ["login" => "ok"]);

} catch (PDOException $e) {
    redirectWith("../PAGINAS/login.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
