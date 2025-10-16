<?php
require_once __DIR__ . "/conexao.php";
header('Content-Type: application/json');

// LISTAR FRETES
if (isset($_GET['acao']) && $_GET['acao'] === 'listar') {
    try {
        $stmt = $pdo->query("SELECT idFrete, bairro, valor, transportadora FROM frete ORDER BY idFrete ASC");
        $fretes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "ok",
            "data" => $fretes
        ]);
    } catch (\Exception $e) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Erro ao buscar fretes: " . $e->getMessage()
        ]);
    }
    exit;
}

// CADASTRAR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $bairro = $_POST["bairro"] ?? "";
        $valor = $_POST["valor"] ?? "";
        $transportadora = $_POST["transportadora"] ?? "";

        if (trim($bairro) === "" || trim($valor) === "") {
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Preencha bairro e valor do frete"
            ]);
            exit;
        }

        $sql = "INSERT INTO frete (bairro, valor, transportadora) VALUES (:bairro, :valor, :transportadora)";
        $stmt = $pdo->prepare($sql);
        $executou = $stmt->execute([
            ":bairro" => $bairro,
            ":valor" => $valor,
            ":transportadora" => $transportadora
        ]);

        if ($executou) {
            echo json_encode([
                "status" => "ok",
                "mensagem" => "Frete cadastrado com sucesso"
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

// SE NENHUM MÉTODO VÁLIDO
echo json_encode([
    "status" => "erro",
    "mensagem" => "Ação inválida"
]);
exit;
