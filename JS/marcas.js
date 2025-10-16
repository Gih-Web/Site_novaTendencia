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

  // --- util 1) esc(): escapa caracteres especiais no texto (evita quebrar o HTML)
   const esc = s => (s||'').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));
 
  // --- util 2) ph(): gera um SVG base64 com as iniciais, usado quando não há imagem
  const ph  = n => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
       <rect width="100%" height="100%" fill="#eee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             font-family="sans-serif" font-size="12" fill="#999">
         ${(n||'?').slice(0,2).toUpperCase()}
       </text>
     </svg>`
  );



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


  // --- util 1) esc(): escapa caracteres especiais no texto (evita quebrar o HTML)
   const esc = s => (s||'').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));
 
  // --- util 2) ph(): gera um SVG base64 com as iniciais, usado quando não há imagem
  const ph  = n => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
       <rect width="100%" height="100%" fill="#eee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             font-family="sans-serif" font-size="12" fill="#999">
         ${(n||'?').slice(0,2).toUpperCase()}
       </text>
     </svg>`
  );


}

// Chamadas corretas:
listarMarcas("#marcasTable tbody");  // tabela
listarNomesMarcas("#pMarca");       // select
