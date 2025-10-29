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
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirectWith("../PAGINAS/login.html", ["erro" => "Método inválido"]);
    }

    // CAPTURA OS CAMPOS DO FORMULÁRIO
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    // validação básica
    if ($email === "" || $senha === "") {
        redirectWith("../PAGINAS/login.html", ["erro" => "Preencha todos os campos"]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWith("../PAGINAS/login.html", ["erro" => "E-mail inválido"]);
    }

    // BUSCA NO BANCO PELO EMAIL
    $stmt = $pdo->prepare("SELECT * FROM CLIENTE WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        redirectWith("../PAGINAS/login.html", ["erro" => "E-mail não encontrado"]);
    }

    // COMPARA SENHA (ainda texto puro)
    if ($usuario["senha"] !== $senha) {
        redirectWith("../PAGINAS/login.html", ["erro" => "Senha incorreta"]);
    }

    // LOGIN BEM-SUCEDIDO → CRIA SESSÃO
    session_start();
    $_SESSION["usuario_id"] = $usuario["idCliente"];
    $_SESSION["usuario_nome"] = $usuario["nome"];

    redirectWith("../index.html", ["login" => "ok"]);

} catch (PDOException $e) {
    redirectWith("../PAGINAS/login.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
