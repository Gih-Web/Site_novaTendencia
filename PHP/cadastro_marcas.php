<?php
// Conectando este arquivo ao banco de dados
require_once __DIR__ . "/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// ==========================================================
// LISTAR MARCAS - para JS (fetch)
if (isset($_GET['listar']) && $_GET['listar'] == 1) {
    try {
        $stmt = $pdo->query("SELECT IdMarcas, nome, imagem FROM MARCAS ORDER BY nome");
        $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($marcas) > 0) {
            foreach ($marcas as $row) {
                $imgSrc = $row['imagem'] ? "data:image/jpeg;base64," . base64_encode($row['imagem']) : "";
                echo '<tr>';
                echo '<td><img src="'.$imgSrc.'" alt="'.htmlspecialchars($row['nome']).'" style="width:50px;height:auto;border-radius:5px;"></td>';
                echo '<td>'.htmlspecialchars($row['nome']).'</td>';
                echo '<td class="text-end">
                        <a href="editar_marca.php?id='.$row['IdMarcas'].'" class="btn btn-sm btn-primary">Editar</a>
                        <a href="excluir_marca.php?id='.$row['IdMarcas'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Deseja realmente excluir?\')">Excluir</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3" class="text-center">Nenhuma marca cadastrada</td></tr>';
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="3" class="text-center">Erro ao carregar marcas</td></tr>';
    }
    exit; // interrompe o resto do script
}

// ==========================================================
// CADASTRO DE MARCA
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
            ["erro_marca" => "Método inválido"]);
    }

    $nomemarca = trim($_POST["nomemarca"] ?? "");
    $imgBlob   = readImageToBlob($_FILES["imagemmarca"] ?? null);

    $erros_validacao = [];
    if ($nomemarca === "") {
        $erros_validacao[] = "Preencha o nome da marca.";
    }

    if (!empty($erros_validacao)) {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
            ["erro_marca" => implode(" ", $erros_validacao)]);
    }

    $sql  = "INSERT INTO MARCAS (nome, imagem) VALUES (:nome, :img)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":nome", $nomemarca, PDO::PARAM_STR);
    if ($imgBlob === null) {
        $stmt->bindValue(":img", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":img", $imgBlob, PDO::PARAM_LOB);
    }

    $ok = $stmt->execute();

    if ($ok) {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
            ["cadastro_marca" => "ok"]);
    } else {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
            ["erro_marca" => "Falha ao cadastrar marca."]);
    }

} catch (Exception $e) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
        ["erro_marca" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>
