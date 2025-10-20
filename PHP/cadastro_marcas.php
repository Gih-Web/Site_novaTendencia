<?php
require_once __DIR__ . '/conexao.php';

// =============================================
// Função auxiliar para redirecionar com parâmetros
// =============================================
function redirect_with(string $url, array $params = []): void {
  if ($params) {
    $qs  = http_build_query($params);
    $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
  }
  header("Location: $url");
  exit;
}

// =============================================
// Função para ler imagem e transformar em blob
// =============================================
function read_image_to_blob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $bin = file_get_contents($file['tmp_name']);
  return $bin === false ? null : $bin;
}

// =============================================
// LISTAGEM DE MARCAS (usada pelo JS via fetch)
// =============================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
  header('Content-Type: application/json; charset=utf-8');

  try {
    $stmt = $pdo->query("SELECT idMarcas, nome, imagem FROM Marcas ORDER BY idMarcas DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $marcas = array_map(function ($r) {
      return [
        'idMarcas' => (int)$r['idMarcas'],
        'nome'     => $r['nome'],
        'imagem'   => !empty($r['imagem']) ? base64_encode($r['imagem']) : null
      ];
    }, $rows);

    echo json_encode([
      'ok' => true,
      'count' => count($marcas),
      'marcas' => $marcas
    ], JSON_UNESCAPED_UNICODE);

  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
  }

  exit;
}


// =====================================================
// LISTAR APENAS NOMES DAS MARCAS (para <select> via JS)
// =====================================================
if (isset($_GET['listarNomes']) && $_GET['listarNomes'] == 1) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $pdo->query("SELECT IdMarcas, nome FROM Marcas ORDER BY nome");
        $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($marcas, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(["erro" => "Erro ao carregar nomes das marcas"]);
    }
    exit;
}


// =============================================
// CADASTRO DE NOVA MARCA
// =============================================
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with('../PAGINAS_LOGISTA/cadastro_marcas_logista.html', [
      'erro_marca' => 'Método inválido'
    ]);
  }

  $nome = trim($_POST['nomemarca'] ?? '');
  $imgBlob = read_image_to_blob($_FILES['imagemmarca'] ?? null);

  if ($nome === '') {
    redirect_with('../PAGINAS_LOGISTA/cadastro_marcas_logista.html', [
      'erro_marca' => 'Preencha o nome da marca.'
    ]);
  }

  $sql = "INSERT INTO Marcas (nome, imagem) VALUES (:n, :i)";
  $st  = $pdo->prepare($sql);
  $st->bindValue(':n', $nome, PDO::PARAM_STR);
  if ($imgBlob === null) $st->bindValue(':i', null, PDO::PARAM_NULL);
  else $st->bindValue(':i', $imgBlob, PDO::PARAM_LOB);
  $st->execute();

  redirect_with('../PAGINAS_LOGISTA/cadastro_marcas_logista.html', [
    'cadastro_marca' => 'ok'
  ]);

} catch (Throwable $e) {
  redirect_with('../PAGINAS_LOGISTA/cadastro_marcas_logista.html', [
    'erro_marca' => 'Erro no banco de dados: ' . $e->getMessage()
  ]);
}
?>
