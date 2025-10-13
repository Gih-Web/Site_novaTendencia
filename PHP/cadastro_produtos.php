<?php
// ==========================================
// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// ==========================================
// Função para redirecionar com parâmetros
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    // Garante que não há saída antes do header
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit;
    }
}

// ==========================================
// Função para ler arquivo como blob
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// ==========================================
// LISTAR PRODUTOS (para JS via fetch)
if (isset($_GET['listar_produtos']) && $_GET['listar_produtos'] == 1) {
    ob_start(); // Impede envio direto de cabeçalhos
    try {
        $sql = "
            SELECT 
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
                (
                    SELECT i.foto 
                    FROM IMAGEM_PRODUTO i
                    JOIN PRODUTO_IMAGEM pi ON pi.imagem_produto = i.idImagem_produto
                    WHERE pi.produto_id = p.idProdutos
                    LIMIT 1
                ) AS imagem
            FROM PRODUTOS p
            LEFT JOIN MARCAS m ON p.marcas_id = m.IdMarcas
            LEFT JOIN PRODUTO_CATEGORIA pc ON pc.produtos_id = p.idProdutos
            LEFT JOIN CATEGORIA c ON pc.categoria_produtos = c.idCategoria
            ORDER BY p.nome
        ";

        $stmt = $pdo->query($sql);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($produtos) > 0) {
            foreach ($produtos as $row) {
                $imgSrc = $row['imagem'] ? "data:image/jpeg;base64," . base64_encode($row['imagem']) : "../IMG/sem-foto.png";

                echo '<tr>';
                echo '<td><img src="'.$imgSrc.'" alt="Imagem do produto" style="width:60px;height:auto;border-radius:6px;"></td>';
                echo '<td>'.htmlspecialchars($row['nome']).'</td>';
                echo '<td>'.htmlspecialchars($row['descricao']).'</td>';
                echo '<td>'.htmlspecialchars($row['quantidade']).'</td>';
                echo '<td>R$ '.number_format($row['preco'], 2, ',', '.').'</td>';
                echo '<td>';
                echo $row['preco_promocional'] ? 'R$ '.number_format($row['preco_promocional'], 2, ',', '.') : '-';
                echo '</td>';
                echo '<td>'.htmlspecialchars($row['tamanho']).'</td>';
                echo '<td>'.htmlspecialchars($row['cor']).'</td>';
                echo '<td>'.htmlspecialchars($row['codigo']).'</td>';
                echo '<td>'.htmlspecialchars($row['marca'] ?? 'Sem marca').'</td>';
                echo '<td>'.htmlspecialchars($row['categoria'] ?? 'Sem categoria').'</td>';
                echo '<td class="text-end">
                        <a href="editar_produto.php?id='.$row['idProdutos'].'" class="btn btn-sm btn-primary">Editar</a>
                        <a href="excluir_produto.php?id='.$row['idProdutos'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Deseja realmente excluir?\')">Excluir</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="12" class="text-center">Nenhum produto cadastrado</td></tr>';
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="12" class="text-center">Erro ao carregar produtos</td></tr>';
    }
    ob_end_flush(); // Libera a saída
    exit;
}

// ==========================================
// CADASTRAR PRODUTO
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => "Método inválido"]);
    }

    // Dados do produto
    $nome = $_POST["nomeproduto"] ?? "";
    $descricao = $_POST["descricao"] ?? "";
    $quantidade = (int)($_POST["quantidade"] ?? 0);
    $preco = (double)($_POST["preco"] ?? 0);
    $tamanho = $_POST["tamanho"] ?? "";
    $cor = $_POST["cor"] ?? "";
    $codigo = (int)($_POST["codigo"] ?? 0);
    $preco_promocional = (double)($_POST["precopromocional"] ?? 0);
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
        (nome, descricao, quantidade, preco, tamanho, cor, preco_promocional, marcas_id, codigo) 
        VALUES (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, :preco_promocional, :marcas_id, :codigo)";
    $stmProdutos = $pdo->prepare($sqlProdutos);
    $inserirProdutos = $stmProdutos->execute([
        ":nome" => $nome,
        ":descricao" => $descricao,
        ":quantidade" => $quantidade,
        ":preco" => $preco,
        ":tamanho" => $tamanho,
        ":cor" => $cor,
        ":preco_promocional" => $preco_promocional,
        ":marcas_id" => $marcas_id,
        ":codigo" => $codigo
    ]);

    if (!$inserirProdutos) {
        $pdo->rollBack();
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => "Falha ao cadastrar produto."]);
    }

    $idproduto = (int)$pdo->lastInsertId();

    // Inserir imagens e vincular ao produto
    foreach ($imagens as $img) {
        if ($img !== null) {
            $sqlImg = "INSERT INTO imagem_produto (foto) VALUES (:foto)";
            $stmImg = $pdo->prepare($sqlImg);
            $stmImg->bindParam(':foto', $img, PDO::PARAM_LOB);
            $stmImg->execute();
            $idImg = (int)$pdo->lastInsertId();

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
    if ($pdo->inTransaction()) $pdo->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro_produto" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>
