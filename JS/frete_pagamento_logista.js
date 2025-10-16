// ================================================
// 1️⃣ Função para listar as formas de pagamento
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

    tbody.innerHTML = "";

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
// 2️⃣ Função para cadastrar forma de pagamento
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
      body: formData
    });

    if (!resposta.ok) throw new Error("Erro ao cadastrar a forma de pagamento.");

    // Atualiza a lista sem recarregar a página
    listarFormasPagamento();
    form.reset();

  } catch (erro) {
    console.error("Erro ao cadastrar:", erro);
    alert("Erro ao cadastrar a forma de pagamento. Verifique o console.");
  }
}

// ================================================
// 3️⃣ Função para listar fretes
// ================================================
async function listarFretes() {
  const tbody = document.querySelector("#fretesTableBody");

  if (!tbody) {
    console.error("Elemento <tbody> da tabela de fretes não encontrado.");
    return;
  }

  try {
    const resposta = await fetch("../PHP/cadastro_frete.php?acao=listar");
    if (!resposta.ok) throw new Error("Falha ao carregar lista de fretes");

    const dados = await resposta.json();

    if (dados.status !== "ok") throw new Error(dados.mensagem || "Erro ao listar fretes");

    tbody.innerHTML = "";

    dados.data.forEach(frete => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${frete.idFrete}</td>
        <td>${frete.bairro}</td>
        <td>${frete.transportadora || '-'}</td>
        <td class="text-end">R$ ${parseFloat(frete.valor).toFixed(2)}</td>
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
        <td colspan="5" class="text-center text-danger">
          Erro ao carregar fretes.
        </td>
      </tr>`;
  }
}

// ================================================
// 4️⃣ Função para cadastrar frete
// ================================================
async function cadastrarFrete(event) {
  event.preventDefault();
  const form = event.target;
  const bairroInput = form.querySelector("#frBairro");
  const valorInput = form.querySelector("#frValor");

  if (!bairroInput.value.trim() || !valorInput.value.trim()) {
    alert("Preencha todos os campos obrigatórios do frete.");
    bairroInput.focus();
    return;
  }

  const formData = new FormData(form);

  try {
    const resposta = await fetch("../PHP/cadastro_frete.php", {
      method: "POST",
      body: formData
    });

    if (!resposta.ok) throw new Error("Erro ao cadastrar o frete.");

    // Atualiza a lista sem recarregar a página
    listarFretes();
    form.reset();

  } catch (erro) {
    console.error("Erro ao cadastrar frete:", erro);
    alert("Erro ao cadastrar frete. Verifique o console.");
  }
}

// ================================================
// 5️⃣ Inicializa tudo ao carregar a página
// ================================================
document.addEventListener("DOMContentLoaded", () => {
  // Lista pagamentos e fretes
  listarFormasPagamento();
  listarFretes();

  // Eventos de envio de formulário
  const formPagamento = document.querySelector("#formPagamento");
  if (formPagamento) {
    formPagamento.addEventListener("submit", cadastrarFormaPagamento);
  }

  const formFrete = document.querySelector("#formFrete");
  if (formFrete) {
    formFrete.addEventListener("submit", cadastrarFrete);
  }
});
