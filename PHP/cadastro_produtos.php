<?php
// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// Função para redirecionar com parâmetros
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Função para ler arquivo como blob
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => "Método inválido"]);
    }

    // Dados do produto
    $nome = $_POST["nomeproduto"];
    $quantidade = (int)$_POST["quantidade"];
    $preco = (double)$_POST["preco"];
    $tamanho = $_POST["tamanho"];
    $cor = $_POST["cor"];
    $codigo = (int)$_POST["codigo"];
    $preco_promocional = (double)$_POST["precopromocional"];
    $marcas_id = 1;

    // Imagens
    $imagens = [
        readImageToBlob($_FILES["imgproduto1"] ?? null),
        readImageToBlob($_FILES["imgproduto2"] ?? null),
        readImageToBlob($_FILES["imgproduto3"] ?? null)
    ];

    // Validação
    $erros_validacao = [];
    if ($nome === "" || $quantidade <= 0 || $preco <= 0 || $marcas_id <= 0) {
        $erros_validacao[] = "Preencha todos os campos obrigatórios corretamente.";
    }
    if (!empty($erros_validacao)) {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => implode(" ", $erros_validacao)]);
    }

    $pdo->beginTransaction();

    // Inserir produto
    $sqlProdutos = "INSERT INTO produtos 
        (nome, quantidade, preco, tamanho, cor, preco_promocional, marcas_id) 
        VALUES (:nome, :quantidade, :preco, :tamanho, :cor, :preco_promocional, :marcas_id)";
    $stmProdutos = $pdo->prepare($sqlProdutos);
    $inserirProdutos = $stmProdutos->execute([
        ":nome" => $nome,
        ":quantidade" => $quantidade,
        ":preco" => $preco,
        ":tamanho" => $tamanho,
        ":cor" => $cor,
        ":preco_promocional" => $preco_promocional,
        ":marcas_id" => $marcas_id
    ]);

    if (!$inserirProdutos) {
        $pdo->rollBack();
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["Erro" => "Falha ao cadastrar produto."]);
    }

    $idproduto = (int)$pdo->lastInsertId();

    // Inserir imagens e vincular ao produto
    foreach ($imagens as $img) {
        if ($img !== null) {
            // Inserir na tabela de imagens
            $sqlImg = "INSERT INTO imagem_produto (foto) VALUES (:foto)";
            $stmImg = $pdo->prepare($sqlImg);
            $stmImg->bindParam(':foto', $img, PDO::PARAM_LOB);
            $stmImg->execute();
            $idImg = (int)$pdo->lastInsertId();

            // Vincular ao produto
            $sqlVinc = "INSERT INTO produto_imagem (produto_id, imagem_produto) VALUES (:produto_id, :idImagem)";
            $stmVinc = $pdo->prepare($sqlVinc);
            $stmVinc->execute([
                ':produto_id' => $idproduto,
                ':idImagem' => $idImg
            ]);
        }
    }

    $pdo->commit();

    redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["sucesso" => "Produto e imagens cadastrados com sucesso."]);

} catch (Exception $e) {
    $pdo->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>
