<?php
require_once __DIR__ . "/conexao.php";

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
        redirectWith("../PAGINAS/cadastro.html", ["erro" => "Método inválido"]);
    }

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $telefone = $_POST["telefone"];
    $cpf = $_POST["cpf"];
    $confirmarsenha = $_POST["confirmarSenha"];

    $erros_validacao = [];

    if ($nome === "" || $email === "" || $senha === "" || $telefone === "" || $cpf === "" || $confirmarsenha === "") {
        $erros_validacao[] = "Preencha todos os campos";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros_validacao[] = "E-mail inválido";
    }

    if ($senha !== $confirmarsenha) {
        $erros_validacao[] = "As senhas não conferem";
    }

    if (strlen($senha) < 8) {
        $erros_validacao[] = "Senha deve ter pelo menos 8 caracteres";
    }

    if (strlen($telefone) < 11) {
        $erros_validacao[] = "Telefone deve ter pelo menos 11 caracteres";
    }

    if (strlen($cpf) < 11) {
        $erros_validacao[] = "O CPF deve ter pelo menos 11 números";
    }

    if ($erros_validacao) {
        redirectWith("../PAGINAS/cadastro.html", ["erro" => $erros_validacao[0]]);
    }

    // verificar se o cpf já existe
    $stmt = $pdo->prepare("SELECT * FROM Cliente WHERE cpf = :cpf LIMIT 1");
    $stmt->execute([':cpf' => $cpf]);

    if ($stmt->fetch()) {
        redirectWith("../PAGINAS/cadastro.html", ["erro" => "CPF já cadastrado"]);
    }

    // inserir
    $sql = "INSERT INTO Cliente (nome, cpf, telefone, email, senha)
            VALUES (:nome, :cpf, :telefone, :email, :senha)";

    $inserir = $pdo->prepare($sql)->execute([
        ":nome" => $nome,
        ":cpf" => $cpf,
        ":telefone" => $telefone,
        ":email" => $email,
        ":senha" => $senha,
    ]);

    if ($inserir) {
        redirectWith("../PAGINAS/login.html", ["cadastro" => "ok"]);
    } else {
        redirectWith("../PAGINAS/cadastro.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
    }

} catch (PDOException $e) {
    redirectWith("../PAGINAS/cadastro.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>
