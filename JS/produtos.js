function listarprodutos(produtos) {
  (async () => {
    const tbody = document.querySelector(produtos);

    try {
      const r = await fetch("../PHP/cadastro_produtos.php?listar_produtos=1");
      if (!r.ok) throw new Error("Falha ao listar produtos!");
      tbody.innerHTML = await r.text();
    } catch (e) {
      tbody.innerHTML = '<tr><td colspan="10" class="text-center">Erro ao carregar produtos</td></tr>';
      console.error(e);
    }
  })();
}

listarprodutos("#produtosTable tbody");
