<?php
require_once __DIR__ . "/conexao.php";
header('Content-Type: application/json');

// ==========================
// LISTAR BANNERS
// ==========================
if (isset($_GET['acao']) && $_GET['acao'] === 'listar') {
    try {
        $stmt = $pdo->query("SELECT B.idBanners, B.descricao, B.link, B.categoria_id, B.data_validade, C.nome AS categoria_nome
                             FROM BANNERS B
                             LEFT JOIN CATEGORIA C ON B.categoria_id = C.idCategoria
                             ORDER BY B.idBanners ASC");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "ok", "data" => $banners]);
    } catch (\Exception $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}

// ==========================
// CADASTRAR BANNER
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $descricao = $_POST['descricao'] ?? '';
        $link = $_POST['link'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? null;
        $data_validade = $_POST['data_validade'] ?? null;

        if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] != 0) {
            echo json_encode(["status" => "erro", "mensagem" => "Envie uma imagem válida"]);
            exit;
        }

        $imagem = file_get_contents($_FILES['imagem']['tmp_name']);

        $sql = "INSERT INTO BANNERS (imagem, descricao, link, categoria_id, data_validade)
                VALUES (:imagem, :descricao, :link, :categoria_id, :data_validade)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":imagem" => $imagem,
            ":descricao" => $descricao,
            ":link" => $link,
            ":categoria_id" => $categoria_id,
            ":data_validade" => $data_validade
        ]);

        echo json_encode(["status" => "ok", "mensagem" => "Banner cadastrado com sucesso"]);

    } catch (\Exception $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}

echo json_encode(["status" => "erro", "mensagem" => "Ação inválida"]);
exit;
