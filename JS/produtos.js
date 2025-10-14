// ===============================
// 1️⃣ Função para listar produtos na tabela
// ===============================
async function listarProdutos() {
  const tbody = document.querySelector("#produtosTable tbody");

  try {
    const r = await fetch("../PHP/cadastro_produtos.php?listar_produtos=1");
    if (!r.ok) throw new Error("Falha ao listar produtos!");
    tbody.innerHTML = await r.text();

    // Adiciona eventos de clique nos botões "Editar"
    tbody.querySelectorAll(".btn-editar-produto").forEach(btn => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const produtoId = btn.dataset.id;
        carregarProduto(produtoId);
      });
    });

  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="12" class="text-center">Erro ao carregar produtos</td></tr>';
    console.error(e);
  }
}

// ===============================
// 2️⃣ Função para carregar dados do produto no formulário
// ===============================
async function carregarProduto(produtoId) {
  try {
    const r = await fetch(`../PHP/cadastro_produtos.php?buscar_produto&id=${produtoId}`);
    if (!r.ok) throw new Error("Erro ao buscar produto!");
    const data = await r.json();
    if (!data || !data.idProdutos) return;

    // Função auxiliar para preencher inputs
    const setValue = (selector, value) => {
      const el = document.querySelector(selector);
      if (el) el.value = value ?? "";
    };

    // Preenchendo os campos
    setValue("#idproduto", data.idProdutos);
    setValue("#pNome", data.nome);
    setValue("#pDescricao", data.descricao);
    setValue("#pQtd", data.quantidade ?? 0);
    setValue("#pPreco", data.preco ?? 0);
    setValue("#pPrecoPromo", data.preco_promocional ?? 0);
    setValue("#pTamanho", data.tamanho);
    setValue("#pCor", data.cor);
    setValue("#pCodigo", data.codigo);
    setValue("#pMarca", data.marcas_id ?? 1);

    // ===============================
    // Preenchimento do select de categoria
    // ===============================
    const selectCategoria = document.querySelector("#prodCategoria");
    if (selectCategoria) {
      // Remove opção temporária anterior (se existir)
      const tempOpt = selectCategoria.querySelector(".temp-cat");
      if (tempOpt) tempOpt.remove();

      if (data.categoria_id && data.categoria_nome) {
        let option = selectCategoria.querySelector(`option[value='${data.categoria_id}']`);
        if (!option) {
          option = document.createElement("option");
          option.value = data.categoria_id;
          option.textContent = data.categoria_nome;
          option.classList.add("temp-cat"); // marca como temporária
          selectCategoria.appendChild(option);
        }
      }

      // Sempre define valor, 0 se não houver categoria
      selectCategoria.value = data.categoria_id ?? 0;
    }

    // ===============================
    // Mostrar imagens ou placeholders
    // ===============================
    const imgs = [
      { imgEl: "#pImg1Prev", phEl: "#pImg1Ph", dataImg: data.imagem },
      { imgEl: "#pImg2Prev", phEl: "#pImg2Ph", dataImg: data.imagem2 },
      { imgEl: "#pImg3Prev", phEl: "#pImg3Ph", dataImg: data.imagem3 },
    ];

    imgs.forEach(({ imgEl, phEl, dataImg }) => {
      const imgElement = document.querySelector(imgEl);
      const placeholder = document.querySelector(phEl);
      if (!imgElement) return;

      if (dataImg) {
        imgElement.src = `data:image/jpeg;base64,${dataImg}`;
        imgElement.classList.remove("d-none");
        placeholder?.classList.add("d-none");
      } else {
        imgElement.src = "";
        imgElement.classList.add("d-none");
        placeholder?.classList.remove("d-none");
      }
    });

    // Scroll até o formulário
    const formEl = document.querySelector("#formProduto");
    if (formEl) formEl.scrollIntoView({ behavior: "smooth" });

  } catch (err) {
    console.error("Erro ao carregar produto:", err);
  }
}

// ===============================
// 3️⃣ Inicializa a listagem ao carregar a página
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  listarProdutos();
});
