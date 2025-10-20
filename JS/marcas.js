// =============================================
// Função para listar as marcas na tabela
// =============================================
function listarMarcas(idTabela) {
  // Espera o DOM carregar antes de executar
  document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.querySelector(idTabela);
    const url = "../PHP/cadastro_marcas.php?listar=1";

    // Função para escapar caracteres especiais e evitar erros no HTML
    const esc = s => (s || "").replace(/[&<>"']/g, c => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    }[c]));

    // Gera uma imagem de placeholder caso não haja imagem cadastrada
    const ph = n => "data:image/svg+xml;base64," + btoa(`
      <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
        <rect width="100%" height="100%" fill="#eee"/>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
              font-family="sans-serif" font-size="12" fill="#999">
          ${(n || '?').slice(0, 2).toUpperCase()}
        </text>
      </svg>
    `);

    // Função que monta uma linha da tabela (<tr>) para cada marca
    const row = m => `
      <tr>
        <td>
          <img
            src="${m.imagem ? `data:image/jpeg;base64,${m.imagem}` : ph(m.nome)}"
            alt="${esc(m.nome || 'Marca')}"
            style="width:60px;height:60px;object-fit:cover;border-radius:8px">
        </td>
        <td>${esc(m.nome || "-")}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${m.idMarcas}">Editar</button>
          <button class="btn btn-sm btn-danger" data-id="${m.idMarcas}">Excluir</button>
        </td>
      </tr>`;

    // Faz o fetch ao PHP
    fetch(url, { cache: "no-store" })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || "Erro ao listar marcas");

        tbody.innerHTML = d.marcas?.length
          ? d.marcas.map(row).join("")
          : `<tr><td colspan="3">Nenhuma marca cadastrada.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="3">Erro: ${esc(err.message)}</td></tr>`;
      });
  });
}

// Chama a função ao carregar a página
listarMarcas("#tabelaMarcas");



// =====================================================
// LISTAR APENAS NOMES DAS MARCAS (para <select>)
// =====================================================
async function listarNomesMarcas(idSelect) {
  // Seleciona o elemento <select> pelo ID
  const select = document.querySelector(idSelect);

  // Mostra mensagem temporária
  select.innerHTML = "<option>Carregando...</option>";

  try {
    // Faz a requisição ao PHP
    const resposta = await fetch("../PHP/cadastro_marcas.php?listarNomes=1");
    if (!resposta.ok) throw new Error("Erro ao buscar marcas");

    // Converte o retorno para JSON
    const dados = await resposta.json();

    // Se tiver erro no retorno
    if (dados.erro) throw new Error(dados.erro);

    // Monta as opções dentro do <select>
    if (dados.length > 0) {
      select.innerHTML = dados
        .map(marca => `<option value="${marca.IdMarcas}">${marca.nome}</option>`)
        .join("");
    } else {
      select.innerHTML = "<option disabled>Nenhuma marca cadastrada</option>";
    }

  } catch (erro) {
    console.error(erro);
    select.innerHTML = "<option disabled>Erro ao carregar marcas</option>";
  }
}

listarNomesMarcas("#selectMarcas");