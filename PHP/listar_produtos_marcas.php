<?php
require_once __DIR__ . "/conexao.php";

// Consulta de produtos
$sqlProdutos = "
  SELECT 
    p.idProdutos, p.nome, p.descricao, p.preco, p.quantidade, 
    p.tamanho, p.cor, p.codigo, 
    m.nome AS marca, ip.idImagem_produto
  FROM produtos p
  LEFT JOIN marcas m ON p.marcas_id = m.IdMarcas
  LEFT JOIN produto_imagem pi ON pi.produto_id = p.idProdutos
  LEFT JOIN imagem_produto ip ON ip.idImagem_produto = pi.imagem_produto
  GROUP BY p.idProdutos
";
$resultProdutos = mysqli_query($conexao, $sqlProdutos);
$produtos = mysqli_fetch_all($resultProdutos, MYSQLI_ASSOC);

// Consulta de marcas
$sqlMarcas = "SELECT IdMarcas, nome FROM marcas";
$resultMarcas = mysqli_query($conexao, $sqlMarcas);
$marcas = mysqli_fetch_all($resultMarcas, MYSQLI_ASSOC);

// Inclui o HTML
include __DIR__ . "/listar_produtos_marcas.html";
?>
