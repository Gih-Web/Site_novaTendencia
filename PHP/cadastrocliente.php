<?php
require_once __DIR__ . "/conexao.php";

// Função para redirecionar com parâmetros
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
        redirectWith("../PAGINAS_CLIENTE/cadastro.html", ["erro" => "Método inválido"]);
    }

    // Captura dos dados
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmarsenha = $_POST["confirmarSenha"] ?? "";
    $telefone = preg_replace("/\D/", "", $_POST["telefone"] ?? "");
    $cpf = preg_replace("/\D/", "", $_POST["cpf"] ?? "");

    $erros_validacao = [];

    // Validações
    if ($nome === "" || $email === "" || $senha === "" || $confirmarsenha === "" || $telefone === "" || $cpf === "") {
        $erros_validacao[] = "Preencha todos os campos.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros_validacao[] = "E-mail inválido.";
    }
    if ($senha !== $confirmarsenha) {
        $erros_validacao[] = "As senhas não conferem.";
    }
    if (strlen($senha) < 8) {
        $erros_validacao[] = "Senha deve ter pelo menos 8 caracteres.";
    }
    if (strlen($telefone) < 10) {
        $erros_validacao[] = "Telefone inválido.";
    }
    if (strlen($cpf) !== 11) {
        $erros_validacao[] = "CPF inválido.";
    }

    if (!empty($erros_validacao)) {
        redirectWith("../PAGINAS_CLIENTE/cadastro.html", ["erro" => $erros_validacao[0]]);
    }

    // Verificar CPF já cadastrado
    $stmt = $pdo->prepare("SELECT 1 FROM Cliente WHERE cpf = :cpf LIMIT 1");
    $stmt->execute([':cpf' => $cpf]);
    if ($stmt->fetch()) {
        redirectWith("../PAGINAS_CLIENTE/cadastro.html", ["erro" => "CPF já cadastrado."]);
    }

    // Inserção no banco (senha em texto puro)
    $sql = "INSERT INTO Cliente (nome, cpf, telefone, email, senha)
            VALUES (:nome, :cpf, :telefone, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    $inserir = $stmt->execute([
        ":nome" => $nome,
        ":cpf" => $cpf,
        ":telefone" => $telefone,
        ":email" => $email,
        ":senha" => $senha // sem hash
    ]);

    if ($inserir) {
        redirectWith("../PAGINAS_CLIENTE/login.html", ["cadastro" => "ok"]);
    } else {
        redirectWith("../PAGINAS_CLIENTE/cadastro.html", ["erro" => "Erro ao cadastrar no banco de dados."]);
    }

} catch (PDOException $e) {
    redirectWith("../PAGINAS_CLIENTE/cadastro.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}

?>
