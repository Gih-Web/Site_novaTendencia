<?php
require_once __DIR__ . "/conexao.php";

// Força retorno JSON
header('Content-Type: application/json');

// ======================================
// 1️⃣ Listar formas de pagamento via GET
// ======================================
if (isset($_GET['acao']) && $_GET['acao'] === 'listar') {
    try {
        $stmt = $pdo->query("SELECT idForma_pagamento, nome FROM forma_pagamento ORDER BY idForma_pagamento ASC");
        $formas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "ok",
            "data" => $formas
        ]);
        exit;
    } catch (\Exception $e) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Erro ao buscar formas de pagamento: " . $e->getMessage()
        ]);
        exit;
    }
}

// ======================================
// 2️⃣ Cadastrar forma de pagamento via POST
// ======================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $nomepagamento = $_POST["nomepagamento"] ?? "";

        // validação simples
        if (trim($nomepagamento) === "") {
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Preencha todos os campos"
            ]);
            exit;
        }

        // Inserir no banco
        $sql = "INSERT INTO forma_pagamento (nome) VALUES (:nomepagamento)";
        $stmt = $pdo->prepare($sql);
        $executou = $stmt->execute([":nomepagamento" => $nomepagamento]);

        if ($executou) {
            echo json_encode([
                "status" => "ok",
                "mensagem" => "Forma de pagamento cadastrada com sucesso"
            ]);
        } else {
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao cadastrar no banco de dados"
            ]);
        }
    } catch (\Exception $e) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Erro no banco de dados: " . $e->getMessage()
        ]);
    }
    exit;
}

// ======================================
// Caso nenhum método válido seja usado
// ======================================
echo json_encode([
    "status" => "erro",
    "mensagem" => "Ação inválida"
]);
exit;
?>
