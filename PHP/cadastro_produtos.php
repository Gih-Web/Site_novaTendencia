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
  //$descricao = $_POST["descricao"];
  $quantidade = (int)$_POST["quantidade"];
  $preco = (double)$_POST["preco"];
  $tamanho = $_POST["tamanho"];
  $cor = $_POST ["cor"];
  $codigo = (int)$_POST["codigo"];
  $preco_promocional = (double)$_POST["precopromocional"];
  $marcas_id = 1;

  // criar variaveis de imagem
   $img1   = readImageToBlob($_FILES["imagproduto1"] ?? null);
   $img2   = readImageToBlob($_FILES["imagproduto2"] ?? null);
   $img3   = readImageToBlob($_FILES["imagproduto3"] ?? null);

  


  // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nome=== "" || $quantidade <=0|| $preco <=0 || $marcas_id <=0 )  {
    $erros_validacao[] = "Preencha o nome da marca.";
  }

  // se houver erros, volta para a tela com a mensagem
  if (!empty($erros_validacao)) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_produto" => implode(" ", $erros_validacao)]);
  }

  // INSERT

  // è utilizado  para fazer vinculo de transações 
  $pdo ->beginTransaction();

  // inserir dentro da tabela produtos

    $sqlProdutos  = "INSERT INTO produtos (nome, descricao, quantidade, preco, tamanho, cor, preco_promocional, marcas_id) 
    VALUES (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, ;preco_promocional, :marcas_id)";

    $stmProdutos = $pdo -> prepare($sqlProdutos);
    
    $inserirProdutos= $stmProdutos -> execute([
      ":nome" =>$nome,
     // ":descricao" =>$descricao,
      ":quantidade" =>(int)$quantidade,
      ":preco" =>$preco,
      ":tamanho" =>$tamanho,
      ":cor"=>$cor,
      ":codigo" =>$codigo,
      ":preco_promocional"=>$preco_promocional,
      ":marcas_id"=>$marcas_id,
    ]);

    if(!$inserirProdutos){
      $pdo ->rollBack();
      redirecWith("../PAGINAS_LOGISTA/cadastro_produtos_logista.html",
      ["Erro" => "Falha ao cadastrar produtos"]);
    }

    $idproduto=(int)$pdo->lastInsertId();


    $sqlImagens ="INSERT INTO Imagem_produtos (foto) VALUES 
    (:imagem1),(:imagem2),(:imagem3),";

    $stmImagens = $pdo -> prepare($sqlImagens);

    if($img1 !== null){
      $stmImagens ->bindParam(':imagem1', $img1, PDO::PARAM_LOB);
    }else{
      $stmImagens->bindValue(':imagem1', null, PDO::PARAM_NULL);
    }

    if($ima2 !== null){
      $stmImagens ->bindParam(':imagem2', $ima2, PDO::PARAM_LOB);
    }else{
      $stmImagens->bindValue(':imagem2', null, PDO::PARAM_NULL);
    }

    if($img3 !== null){
      $stmImagens ->bindParam(':imagem3', $img3, PDO::PARAM_LOB);
    }else{
      $stmImagens->bindValue(':imagem3', null, PDO::PARAM_NULL);
    }

    
    $inserirImagens=$stmImagens->execute();

      if(!$inserirImagem){
      $pdo ->rollBack();
      redirecWith("../PAGINAS_LOGISTA/cadastro_produtos_logista.html",
      ["Erro" => "Falha ao cadastrar imagem"]);
    }

    // CASO TENHA DADO CERTO CAPTURE O ID CA IMAGEM CADASTRADA
    $idImg = (int) $pdo->lastInsertId();

    //VINCULAR A IMAGEM COM O PRODUTO
    $sqlImagens ="INSERT INTO PRODUTO_IMAGEM (produto_id, imagem_produto) VALUES 
    (:idpro,idimg)";

    $stmVincularProdImg=$pdo -> prepare($sqlVincularProdImg);

    $inserirVincularProdImg=$stmVincularProdImg->execute([
      ":idpro"=> $idproduto,
      ":idimg"=> $idImg,
    ]);


    if(!$inserirVincularProdImg){

      $pdo ->rollBack();
      redirecWith("../PAGINAS_LOGISTA/cadastro_produtos_logista.html",
      ["Erro" => "Falha ao vincular produto com imagem."]);

    }
    
} catch (Exception $e) {
  redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    ["erro_produto" => "Erro no banco de dados: " . $e->getMessage()]);
}


?>