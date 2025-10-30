<?php
require_once __DIR__ . "/conexao.php";

// Função para redirecionamento com parâmetros
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Função para ler upload de imagem
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try {
    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "POST") {
        // Cadastro ou atualização
        $id = $_POST["id"] ?? null;
        $descricao = $_POST["descricao"] ?? "";
        $link = $_POST["link"] ?? "";
        $categoria = $_POST["categoria"] ?? null;
        $validade = $_POST["validade"] ?? null;

        $img = readImageToBlob($_FILES["imagem"] ?? null);

        // Validação mínima
        if ($descricao === "" || $link === "" || !$img) {
            redirecWith("../paginas_logista/promocoes_logista.html", ["erro" => "Preencha todos os campos e selecione uma imagem."]);
        }

        if ($id) {
            // Atualização
            $sql = "UPDATE banners SET descricao=:descricao, link=:link, categoria_id=:categoria, validade=:validade, imagem=:imagem WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":descricao"=>$descricao,
                ":link"=>$link,
                ":categoria"=>$categoria ?: null,
                ":validade"=>$validade ?: null,
                ":imagem"=>$img,
                ":id"=>$id
            ]);
        } else {
            // Inserção
            $sql = "INSERT INTO banners (descricao, link, categoria_id, validade, imagem) VALUES (:descricao,:link,:categoria,:validade,:imagem)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":descricao"=>$descricao,
                ":link"=>$link,
                ":categoria"=>$categoria ?: null,
                ":validade"=>$validade ?: null,
                ":imagem"=>$img
            ]);
        }

        redirecWith("../paginas_logista/promocoes_logista.html", ["ok"=>1]);
    } elseif ($method === "GET" && isset($_GET["listar"])) {
        // Listagem para JS
        $stmt = $pdo->query("SELECT b.id, b.descricao, b.link, b.validade, b.categoria_id, c.nome as categoria_nome FROM banners b LEFT JOIN categorias c ON b.categoria_id = c.id ORDER BY b.id DESC");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($banners);
    } elseif ($method === "GET" && isset($_GET["categorias"])) {
        // Lista de categorias para select
        $stmt = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome");
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cats);
    } elseif ($method === "POST" && isset($_POST["excluir_id"])) {
        // Exclusão
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id=:id");
        $stmt->execute([":id"=>$_POST["excluir_id"]]);
        echo json_encode(["ok"=>true]);
    }

} catch (Exception $e) {
    echo json_encode(["erro"=>$e->getMessage()]);
}
