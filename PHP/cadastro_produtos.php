<?php
require_once __DIR__ . "/conexao.php";

// ==========================================
// Função para redirecionar
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit;
    }
}

// ==========================================
// Função para ler imagem
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// ==========================================
// LISTAR PRODUTOS
if (isset($_GET['listar_produtos']) && $_GET['listar_produtos'] == 1) {
    ob_start();
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
                (
                    SELECT c.nome 
                    FROM categoria c 
                    JOIN produto_categoria pc ON pc.categoria_produtos = c.idCategoria
                    WHERE pc.produtos_id = p.idProdutos
                    LIMIT 1
                ) AS categoria,
                (
                    SELECT i.foto 
                    FROM imagem_produto i
                    JOIN produto_imagem pi ON pi.imagem_produto = i.idImagem_produto
                    WHERE pi.produto_id = p.idProdutos
                    LIMIT 1
                ) AS imagem
            FROM produtos p
            LEFT JOIN marcas m ON p.marcas_id = m.IdMarcas
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
                echo '<td>'.($row['preco_promocional'] ? 'R$ '.number_format($row['preco_promocional'], 2, ',', '.') : '-').'</td>';
                echo '<td>'.htmlspecialchars($row['tamanho']).'</td>';
                echo '<td>'.htmlspecialchars($row['cor']).'</td>';
                echo '<td>'.htmlspecialchars($row['codigo']).'</td>';
                echo '<td>'.htmlspecialchars($row['marca'] ?? 'Sem marca').'</td>';
                echo '<td>'.htmlspecialchars($row['categoria'] ?? 'Sem categoria').'</td>';
                echo '<td class="text-end">
                        <button type="button" class="btn btn-sm btn-primary btn-editar-produto" data-id="'.$row['idProdutos'].'">Editar</button>
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
    ob_end_flush();
    exit;
}

// ==========================================
// CARREGAR PRODUTO PARA EDIÇÃO
if (isset($_GET['buscar_produto']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("
        SELECT 
            p.*, 
            (SELECT pc.categoria_produtos 
             FROM produto_categoria pc 
             WHERE pc.produtos_id = p.idProdutos 
             LIMIT 1) AS categoria_id,
            (SELECT c.nome 
             FROM categoria c 
             JOIN produto_categoria pc ON pc.categoria_produtos = c.idCategoria
             WHERE pc.produtos_id = p.idProdutos 
             LIMIT 1) AS categoria_nome,
            (
                SELECT i.foto
                FROM imagem_produto i
                JOIN produto_imagem pi ON pi.imagem_produto = i.idImagem_produto
                WHERE pi.produto_id = p.idProdutos
                LIMIT 1
            ) AS imagem
        FROM produtos p
        WHERE p.idProdutos = :id
    ");
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Converte imagem em base64 se existir
    if ($produto && $produto['imagem']) {
        $produto['imagem'] = base64_encode($produto['imagem']);
    } else {
        $produto['imagem'] = null;
    }

    echo json_encode($produto ?: []);
    exit;
}

// ==========================================
// CADASTRAR / EDITAR PRODUTO
// ... (mantém seu código de cadastro/edição)
?>
