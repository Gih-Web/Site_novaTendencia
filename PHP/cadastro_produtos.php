<?php
// Conectando este arquivo ao banco de dados
require_once __DIR__ . "/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url, $params = []) {
  // verifica se os os paramentros não vieram vazios
  if (!empty($params)) {
    // separar os parametros em espaços diferentes
    $qs  = http_build_query($params);
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    $url .= $sep . $qs;
  }
  // joga a url para o cabeçalho no navegador
  header("Location: $url");
  // fecha o script
  exit;
}


/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

try {
  // SE O METODO DE ENVIO FOR DIFERENTE DO POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_protudo" => "Método inválido"]);
  }

  // jogando os dados dentro de váriaveis (conforme seu HTML)
  //criar as variáveis
  $nome = $_POST["nomeproduto"];
  $descricao = $_POST["descricao"];
  $quantidade = (int)$_POST["quantidade"];
  $preco = (double)$_POST["preco"];
  $tamanho = $_POST["tamanho"];
  $cor = $_POST ["cor"];
  $codigo = (int)$_POST["codigo"];
  $preco_promocional = (double)$POST["precopromocional"];
  $marcas_id = 1;


  // criar variaveis de imagem
   $img1   = readImageToBlob($_FILES["imagproduto1"] ?? null);
   $img2   = readImageToBlob($_FILES["imagproduto2"] ?? null);
   $img3   = readImageToBlob($_FILES["imagproduto3"] ?? null);

  // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nome=== "" || $descricao=== "" || $quantidade=0|| $preco=0 )  {
    $erros_validacao[] = "Preencha o nome da marca.";
  }

  // se houver erros, volta para a tela com a mensagem
  if (!empty($erros_validacao)) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_produto" => implode(" ", $erros_validacao)]);
  }

  // INSERT

  // è utilizado  para fazer vinculo de transações 
  $pdo  ->beginTransaction();

  // inserir dentro da tabela produtos

    $sql  = "INSERT INTO produtos (nome, descricao, quantidade, preco, tamanho, cor, preco_promocional, marcas_id) 
    VALUES (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, ;preco_promocional, :marcas_id)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":nome", $nomemarca, PDO::PARAM_STR);


    $stmProdutos = $pdo -> prepare($sqlProdutos);

    $inserirProdutos= $stmProdutos -> execute([

    ]);

} catch (Exception $e) {
  redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    ["erro_produto" => "Erro no banco de dados: " . $e->getMessage()]);
}


?>