// Função para listar marcas (tabela com imagem)
function listarMarcas(nomeid) {
  (async () => {
    const tbody = document.querySelector(nomeid); // sem vírgula!

    try {
      const r = await fetch("../PHP/cadastro_marcas.php?listar=1");
      if (!r.ok) throw new Error("Falha ao listar marcas!");

      tbody.innerHTML = await r.text();
    } catch (e) {
      tbody.innerHTML =
        '<tr><td colspan="3" class="text-center">Erro ao carregar marcas</td></tr>';
      console.error(e);
    }
  })();
}

// Função para listar apenas nomes das marcas no select
function listarNomesMarcas(idSelect) {
  (async () => {
    const select = document.querySelector(idSelect);

    try {
      const r = await fetch("../PHP/cadastro_marcas.php?listarNomes=1");
      if (!r.ok) throw new Error("Falha ao listar nomes!");

      select.innerHTML = await r.text(); // insere os <option>
    } catch (e) {
      select.innerHTML = "<option disabled>Erro ao carregar</option>";
      console.error(e);
    }
  })();
}

// Chamadas corretas:
listarMarcas("#marcasTable tbody");  // tabela
listarNomesMarcas("#pMarca");       // select
