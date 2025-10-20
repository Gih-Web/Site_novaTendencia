<?php
header('Content-Type: application/json; charset=utf-8');

// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// Função para ler upload de imagem
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// Função de redirecionamento para ações via formulário (não JSON)
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Define action: GET ou POST
$action = $_REQUEST['action'] ?? '';

try {
    if ($action === 'cadastrar') {
        // ==========================
        // CADASTRO DE PRODUTO
        // ==========================

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Método inválido"]);
        }

        // Captura dados do formulário
        $nome   = $_POST["nomeproduto"] ?? '';
        $descricao = $_POST["descricao"] ?? '';
        $quantidade = (int)($_POST["quantidade"] ?? 0);
        $preco  = (float)($_POST["preco"] ?? 0);
        $tamanho = $_POST["tamanho"] ?? '';
        $cor     = $_POST["cor"] ?? '';
        $codigo  = (int)($_POST["codigo"] ?? 0);
        $preco_promocional = (float)($_POST["precopromocional"] ?? 0);
        $categoria_id = (int)($_POST["categoriaproduto"] ?? 0);
        $marca_id = (int)($_POST["pMarcas"] ?? 0);

        // Imagens
        $img1 = readImageToBlob($_FILES["imgproduto1"] ?? null);
        $img2 = readImageToBlob($_FILES["imgproduto2"] ?? null);
        $img3 = readImageToBlob($_FILES["imgproduto3"] ?? null);

        // Validação
        $erros = [];
        if ($nome === '' || $descricao === '' || $quantidade <= 0 || $preco <= 0 || $categoria_id <= 0 || $marca_id <= 0) {
            $erros[] = "Preencha os campos obrigatórios.";
        }
        if (!empty($erros)) {
            redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => implode(" ", $erros)]);
        }

        $pdo->beginTransaction();

        // INSERT produto
        $sqlProd = "INSERT INTO Produtos 
            (nome, descricao, quantidade, preco, tamanho, cor, codigo, preco_promocional, Marcas_idMarcas, Categorias_idCategorias)
            VALUES (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, :codigo, :preco_promocional, :marca, :categoria)";
        $stmtProd = $pdo->prepare($sqlProd);
        $stmtProd->execute([
            ":nome"=>$nome,
            ":descricao"=>$descricao,
            ":quantidade"=>$quantidade,
            ":preco"=>$preco,
            ":tamanho"=>$tamanho,
            ":cor"=>$cor,
            ":codigo"=>$codigo,
            ":preco_promocional"=>$preco_promocional ?: null,
            ":marca"=>$marca_id,
            ":categoria"=>$categoria_id
        ]);

        $idProduto = (int)$pdo->lastInsertId();

        // INSERT imagens
        $sqlImg = "INSERT INTO Imagem_produtos (foto, Produtos_idProdutos) VALUES (:img, :produto)";
        $stmtImg = $pdo->prepare($sqlImg);

        foreach ([$img1, $img2, $img3] as $img) {
            $stmtImg->bindParam(':produto', $idProduto, PDO::PARAM_INT);
            if ($img !== null) {
                $stmtImg->bindParam(':img', $img, PDO::PARAM_LOB);
            } else {
                $stmtImg->bindValue(':img', null, PDO::PARAM_NULL);
            }
            $stmtImg->execute();
        }

        $pdo->commit();
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["Cadastro" => "ok"]);

    } elseif ($action === 'listar_produtos') {
        // ==========================
        // LISTAGEM DE PRODUTOS
        // ==========================
        $sql = "SELECT p.idProdutos, p.nome, p.descricao, p.quantidade, p.preco, p.preco_promocional,
                       p.tamanho, p.cor, p.codigo,
                       m.nomemarca, c.nomecategoria,
                       ip.foto AS imagem
                FROM Produtos p
                LEFT JOIN Marcas m ON p.Marcas_idMarcas = m.idMarcas
                LEFT JOIN Categorias c ON p.Categorias_idCategorias = c.idCategorias
                LEFT JOIN Imagem_produtos ip ON ip.Produtos_idProdutos = p.idProdutos
                GROUP BY p.idProdutos"; // retorna uma imagem por produto
        $stmt = $pdo->query($sql);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte imagem binária para base64
        foreach ($produtos as &$prod) {
            $prod['imagem'] = $prod['imagem'] ? 'data:image/jpeg;base64,'.base64_encode($prod['imagem']) : null;
        }
        echo json_encode($produtos);

    } elseif ($action === 'listar_categorias') {
        // ==========================
        // LISTAGEM DE CATEGORIAS
        // ==========================
        $stmt = $pdo->query("SELECT idCategorias, nomecategoria FROM Categorias");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($action === 'listar_marcas') {
        // ==========================
        // LISTAGEM DE MARCAS
        // ==========================
        $stmt = $pdo->query("SELECT idMarcas, nomemarca FROM Marcas");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } else {
        echo json_encode(["erro"=>"Ação inválida"]);
    }

} catch (Exception $e) {
    echo json_encode(["erro"=>"Erro no banco de dados: ".$e->getMessage()]);
}
