<?php
require_once __DIR__ . '/conexao.php';

// ================================================
// FUNÇÃO AUXILIAR: Redirecionamento com parâmetros
// ================================================
function redirect_with(string $url, array $params = []): void {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
    }
    header("Location: $url");
    exit;
}

// ================================================
// FUNÇÃO AUXILIAR: Ler imagem e converter em BLOB
// ================================================
function read_image_to_blob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $bin = file_get_contents($file['tmp_name']);
    return $bin === false ? null : $bin;
}

// ================================================
// LISTAGEM DE PRODUTOS (GET ?listar=1)
// ================================================
if (isset($_GET['listar']) && $_GET['listar'] == 1) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $pdo->query("
            SELECT p.idProdutos AS idProduto,
                   p.nome,
                   p.descricao,
                   p.quantidade,
                   p.preco,
                   p.preco_promocional,
                   p.tamanho,
                   p.cor,
                   p.codigo,
                   c.nome AS categoria,
                   m.nome AS marca,
                   ip.foto AS imagem
            FROM PRODUTOS p
            LEFT JOIN PRODUTO_CATEGORIA pc ON p.idProdutos = pc.produtos_id
            LEFT JOIN CATEGORIA c ON pc.categoria_produtos = c.idCategoria
            LEFT JOIN MARCAS m ON p.marcas_id = m.IdMarcas
            LEFT JOIN PRODUTO_IMAGEM pim ON p.idProdutos = pim.produto_id
            LEFT JOIN IMAGEM_PRODUTO ip ON pim.imagem_produto = ip.idImagem_produto
            GROUP BY p.idProdutos
            ORDER BY p.idProdutos DESC
        ");

        $produtos = array_map(function($p) {
            return [
                'idProduto' => (int)$p['idProduto'],
                'nome' => $p['nome'],
                'descricao' => $p['descricao'],
                'quantidade' => (int)$p['quantidade'],
                'preco' => (float)$p['preco'],
                'precoPromocional' => $p['preco_promocional'] !== null ? (float)$p['preco_promocional'] : null,
                'tamanho' => $p['tamanho'],
                'cor' => $p['cor'],
                'codigo' => $p['codigo'],
                'categoria' => $p['categoria'] ?? '-',
                'marca' => $p['marca'] ?? '-',
                'imagem' => !empty($p['imagem']) ? base64_encode($p['imagem']) : null
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        echo json_encode(['ok' => true, 'produtos' => $produtos], JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }

    exit; // Muito importante: impede que HTML seja retornado
}

// ================================================
// CADASTRO DE PRODUTOS (POST)
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nomeproduto'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $quantidade  = (int)($_POST['quantidade'] ?? 0);
    $preco       = (float)($_POST['preco'] ?? 0);
    $tamanho     = trim($_POST['tamanho'] ?? '');
    $cor         = trim($_POST['cor'] ?? '');
    $codigo      = trim($_POST['codigo'] ?? '');
    $precoPromo  = $_POST['precopromocional'] !== '' ? (float)$_POST['precopromocional'] : null;
    $categoriaId = (int)($_POST['categoriaproduto'] ?? 0);
    $marcaId     = (int)($_POST['marcaproduto'] ?? 0);

    // Validação mínima
    if ($nome === '' || $quantidade <= 0 || $preco <= 0 || $codigo === '' || $marcaId <= 0) {
        redirect_with('../PAGINAS_LOGISTA/produtos_logista.html', [
            'erro_produto' => 'Preencha todos os campos obrigatórios.'
        ]);
    }

    try {
        $pdo->beginTransaction();

        // Inserir produto
        $sqlProduto = "INSERT INTO PRODUTOS
            (nome, descricao, quantidade, preco, tamanho, cor, codigo, preco_promocional, marcas_id)
            VALUES (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, :codigo, :precoPromo, :marcaId)";

        $stmt = $pdo->prepare($sqlProduto);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':preco', $preco);
        $stmt->bindValue(':tamanho', $tamanho);
        $stmt->bindValue(':cor', $cor);
        $stmt->bindValue(':codigo', $codigo);
        if ($precoPromo === null) $stmt->bindValue(':precoPromo', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':precoPromo', $precoPromo);
        $stmt->bindValue(':marcaId', $marcaId, PDO::PARAM_INT);
        $stmt->execute();

        $produtoId = (int)$pdo->lastInsertId();

        // Inserir imagens
        $imagens = [
            $_FILES['imgproduto1'] ?? null,
            $_FILES['imgproduto2'] ?? null,
            $_FILES['imgproduto3'] ?? null
        ];

        foreach ($imagens as $img) {
            $blob = read_image_to_blob($img);
            if ($blob === null) continue;

            $sqlImg = "INSERT INTO IMAGEM_PRODUTO (foto, texto_alternativo)
                       VALUES (:foto, :alt)";
            $stmtImg = $pdo->prepare($sqlImg);
            $stmtImg->bindValue(':foto', $blob, PDO::PARAM_LOB);
            $stmtImg->bindValue(':alt', $nome);
            $stmtImg->execute();

            $imgId = (int)$pdo->lastInsertId();

            $sqlRel = "INSERT INTO PRODUTO_IMAGEM (produto_id, imagem_produto)
                       VALUES (:produto, :imagem)";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->bindValue(':produto', $produtoId, PDO::PARAM_INT);
            $stmtRel->bindValue(':imagem', $imgId, PDO::PARAM_INT);
            $stmtRel->execute();
        }

        // Relacionar produto com categoria
        if ($categoriaId > 0) {
            $sqlCat = "INSERT INTO PRODUTO_CATEGORIA (produtos_id, produtos_marcas_id, categoria_produtos)
                       VALUES (:prod, :marca, :cat)";
            $stmtCat = $pdo->prepare($sqlCat);
            $stmtCat->bindValue(':prod', $produtoId, PDO::PARAM_INT);
            $stmtCat->bindValue(':marca', $marcaId, PDO::PARAM_INT);
            $stmtCat->bindValue(':cat', $categoriaId, PDO::PARAM_INT);
            $stmtCat->execute();
        }

        $pdo->commit();

        redirect_with('../PAGINAS_LOGISTA/produtos_logista.html', [
            'cadastro_produto' => 'ok'
        ]);

    } catch (Throwable $e) {
        $pdo->rollBack();
        redirect_with('../PAGINAS_LOGISTA/produtos_logista.html', [
            'erro_produto' => 'Erro ao cadastrar: ' . $e->getMessage()
        ]);
    }
}
?>
