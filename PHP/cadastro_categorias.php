<?php
// Conectando ao banco de dados
require_once __DIR__ ."/conexao.php";

function redirecWith($url, $params = []){
  if(!empty($params)){
    $qs = http_build_query($params);
    $sep = (strpos($url,'?') === false) ? '?' : '&';
    $url .= $sep . $qs;
  }
  header("Location: $url");
  exit;
}

// ========================= LISTAGEM DE CATEGORIAS ========================= //
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
   try{
     $sqllistar ="SELECT idCategoria AS id, nome FROM CATEGORIA ORDER BY nome";
     $stmtlistar = $pdo->query($sqllistar);   
     $listar = $stmtlistar->fetchAll(PDO::FETCH_ASSOC);

     $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

     if ($formato === "json") {
       header("Content-Type: application/json; charset=utf-8");
       echo json_encode(["ok" => true, "categorias" => $listar], JSON_UNESCAPED_UNICODE);
       exit;
     }

     header('Content-Type: text/html; charset=utf-8');
     foreach ($listar as $lista) {
       $id   = (int)$lista["id"];
       $nome = htmlspecialchars($lista["nome"], ENT_QUOTES, "UTF-8");
       echo "<option value=\"{$id}\">{$nome}</option>\n";
     }
     exit;

   } catch (Throwable $e) {
     if (isset($_GET['format']) && strtolower($_GET['format']) === 'json') {
       header('Content-Type: application/json; charset=utf-8', true, 500);
       echo json_encode(['ok' => false, 'error' => 'Erro ao listar categorias', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
     } else {
       header('Content-Type: text/html; charset=utf-8', true, 500);
       echo "<option disabled>Erro ao carregar categorias</option>";
     }
     exit;
   }
}

// ========================= CADASTRO DE CATEGORIA ========================= //
try{
  if($_SERVER["REQUEST_METHOD"] !== "POST"){
      redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro"=> "Metodo inválido"]);
  }

  $nome = $_POST["nomecategoria"] ?? '';
  $desconto = (double)($_POST["desconto"] ?? 0);

  $erros_validacao = [];
  if($nome === ""){
      $erros_validacao[] = "Preencha todos os campos";
  }

  if(!empty($erros_validacao)){
      redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro"=> implode(" ", $erros_validacao)]);
  }

  $sql ="INSERT INTO CATEGORIA (nome, desconto) VALUES (:nome, :desconto)";
  $inserir = $pdo->prepare($sql)->execute([
     ":nome" => $nome,
     ":desconto"=> $desconto
  ]);

  if($inserir){
     redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["cadastro" => "ok"]);
  } else {
     redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
  }

} catch(Exception $e){
  redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Erro no banco de dados: ".$e->getMessage()]);
}

// ========================= CONSULTA AUXILIAR PARA <OPTION> ========================= //
try {
  $sql = "SELECT idCategoria, nome FROM CATEGORIA ORDER BY nome";
  foreach ($pdo->query($sql) as $row) {
    $id = (int)$row['idCategoria'];
    $nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
    echo "<option value=\"{$id}\">{$nome}</option>\n";
  }
} catch (Throwable $e) {
  http_response_code(500);
}
?>
