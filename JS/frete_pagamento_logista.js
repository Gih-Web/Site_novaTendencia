// ================================================
// 1️⃣ Função para listar as formas de pagamento via PHP
// ================================================
async function listarFormasPagamento() {
  const tbody = document.querySelector("#pagamentosTableBody");

  if (!tbody) {
    console.error("Elemento <tbody> da tabela de pagamentos não encontrado.");
    return;
  }

  try {
    const resposta = await fetch("../PHP/cadastro_formas_pagamento.php?acao=listar");
    if (!resposta.ok) throw new Error("Falha ao carregar lista de pagamentos");

    const dados = await resposta.json();

    if (dados.status !== "ok") throw new Error(dados.mensagem || "Erro ao listar formas de pagamento");

    // Limpa o tbody
    tbody.innerHTML = "";

    // Preenche a tabela com os dados do banco
    dados.data.forEach(pagamento => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${pagamento.idForma_pagamento}</td>
        <td>${pagamento.nome}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-secondary">Editar</button>
          <button class="btn btn-sm btn-outline-danger">Excluir</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

  } catch (erro) {
    console.error(erro);
    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center text-danger">
          Erro ao carregar formas de pagamento.
        </td>
      </tr>`;
  }
}

// ================================================
// 2️⃣ Função para enviar o formulário de cadastro
// ================================================
async function cadastrarFormaPagamento(event) {
  event.preventDefault();

  const form = event.target;
  const nomeInput = form.querySelector("#fpNome");
  const nomepagamento = nomeInput?.value.trim();

  if (!nomepagamento) {
    alert("Preencha o nome da forma de pagamento.");
    nomeInput.focus();
    return;
  }

  const formData = new FormData(form);

  try {
    const resposta = await fetch("../PHP/cadastro_formas_pagamento.php", {
      method: "POST",
      body: formData,
    });

    if (!resposta.ok) throw new Error("Erro ao cadastrar a forma de pagamento.");

    // ✅ Recarrega a página após cadastro
    location.reload();

  } catch (erro) {
    console.error("Erro ao cadastrar:", erro);
    alert("Erro ao cadastrar a forma de pagamento. Verifique o console.");
  }
}

// ================================================
// 3️⃣ Inicializa tudo ao carregar a página
// ================================================
document.addEventListener("DOMContentLoaded", () => {
  listarFormasPagamento();

  const form = document.querySelector("#formPagamento");
  if (form) {
    form.addEventListener("submit", cadastrarFormaPagamento);
  }
});
