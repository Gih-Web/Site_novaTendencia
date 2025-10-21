<?php
require_once __DIR__ . '/conexao.php';

// ====================================================
// Função auxiliar para redirecionar com parâmetros
// ====================================================
function redirect_with(string $url, array $params = []): void {
  if (!empty($params)) {
    $qs  = http_build_query($params);
    $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
  }
  header("Location: $url");
  exit;
}

// ====================================================
// Função para ler imagem e converter em blob
// ====================================================
function read_image_to_blob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
    return null;
  }
  $bin = file_get_contents($file['tmp_name']);
  return $bin === false ? null : $bin;
}



try {


  if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listarProdutos"])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Query para pegar produtos + marca + categoria + primeira imagem
        $sql = "SELECT 
                    p.idProdutos,
                    p.nome,
                    p.descricao,
                    p.quantidade,
                    p.preco,
                    p.preco_promocional,
                    p.tamanho,
                    p.cor,
                    p.codigo,
                    m.nome AS marca,
                    c.nome AS categoria,
                    ip.foto AS imagem
                FROM PRODUTOS p
                LEFT JOIN MARCAS m ON p.marcas_id = m.IdMarcas
                LEFT JOIN PRODUTO_CATEGORIA pc ON p.idProdutos = pc.produtos_id
                LEFT JOIN CATEGORIA c ON pc.categoria_produtos = c.idCategoria
                LEFT JOIN PRODUTO_IMAGEM pi ON p.idProdutos = pi.produto_id
                LEFT JOIN IMAGEM_PRODUTO ip ON pi.imagem_produto = ip.idImagem_produto
                GROUP BY p.idProdutos
                ORDER BY p.idProdutos DESC";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $produtos = array_map(function ($r) {
            return [
                'idProdutos'           => (int)$r['idProdutos'],
                'nome'                 => $r['nome'],
                'descricao'            => $r['descricao'],
                'quantidade'           => (int)$r['quantidade'],
                'preco'                => (float)$r['preco'],
                'preco_promocional'    => isset($r['preco_promocional']) ? (float)$r['preco_promocional'] : null,
                'tamanho'              => $r['tamanho'],
                'cor'                  => $r['cor'],
                'codigo'               => $r['codigo'],
                'marca'                => $r['marca'],
                'categoria'            => $r['categoria'] ?? 'Sem categoria',
                'imagem'               => !empty($r['imagem']) ? base64_encode($r['imagem']) : null
            ];
        }, $rows);

        echo json_encode([
            'ok' => true,
            'count' => count($produtos),
            'produtos' => $produtos
        ], JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }

    exit;
  }



    // ====================================================
// Somente permite POST
// ====================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect_with('../PAGINAS_LOGISTA/cadastro_produtos_logista.html', [
    'erro_produto' => 'Método inválido'
  ]);
}


// ====================================================
// Captura de campos do formulário
// ====================================================
$nome        = trim($_POST['nomeproduto'] ?? '');
$descricao   = trim($_POST['descricao'] ?? '');
$quantidade  = (int)($_POST['quantidade'] ?? 0);
$preco       = (float)($_POST['preco'] ?? 0);
$tamanho     = trim($_POST['tamanho'] ?? '');
$cor         = trim($_POST['cor'] ?? '');
$codigo      = (int)($_POST['codigo'] ?? 0);
$precoPromo  = $_POST['precopromocional'] !== '' ? (float)$_POST['precopromocional'] : null;
$categoriaId = (int)($_POST['categoriaproduto'] ?? 0);
$marcaId     = (int)($_POST['marcaproduto'] ?? 0);

// ====================================================
// Validação básica
// ====================================================
if ($nome === '' || $quantidade <= 0 || $preco <= 0 || $codigo <=0 || $marcaId <= 0) {
  redirect_with('../PAGINAS_LOGISTA/cadastro_produtos_logista.html', [
    'erro_produto' => 'Preencha todos os campos obrigatórios.'
  ]);
}
  // ====================================================
  // Inicia transação (garantia de integridade)
  // ====================================================
  $pdo->beginTransaction();




  // ====================================================
  // 1. Inserir produto na tabela PRODUTOS
  // ====================================================
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

  // ====================================================
  // 2. Inserir as imagens (se existirem)
  // ====================================================
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

    // Vincular imagem ao produto
    $sqlRel = "INSERT INTO PRODUTO_IMAGEM (produto_id, imagem_produto)
               VALUES (:produto, :imagem)";
    $stmtRel = $pdo->prepare($sqlRel);
    $stmtRel->bindValue(':produto', $produtoId, PDO::PARAM_INT);
    $stmtRel->bindValue(':imagem', $imgId, PDO::PARAM_INT);
    $stmtRel->execute();
  }

  // ====================================================
  // 3. Relacionar produto com categoria
  // ====================================================
  if ($categoriaId > 0) {
    $sqlCat = "INSERT INTO PRODUTO_CATEGORIA (produtos_id, produtos_marcas_id, categoria_produtos)
               VALUES (:prod, :marca, :cat)";
    $stmtCat = $pdo->prepare($sqlCat);
    $stmtCat->bindValue(':prod', $produtoId, PDO::PARAM_INT);
    $stmtCat->bindValue(':marca', $marcaId, PDO::PARAM_INT);
    $stmtCat->bindValue(':cat', $categoriaId, PDO::PARAM_INT);
    $stmtCat->execute();
  }

  // ====================================================
  // Concluir transação
  // ====================================================
  $pdo->commit();

  redirect_with('../PAGINAS_LOGISTA/cadastro_produtos_logista.html', [
    'cadastro_produto' => 'ok'
  ]);

} catch (Throwable $e) {
  $pdo->rollBack();
  redirect_with('../PAGINAS_LOGISTA/cadastro_produtos_logista.html', [
    'erro_produto' => 'Erro ao cadastrar: ' . $e->getMessage()
  ]);
}

?>
