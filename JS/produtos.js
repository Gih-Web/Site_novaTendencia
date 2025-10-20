// ============================================================
// 1️⃣ FUNÇÃO PARA LISTAR PRODUTOS NA TABELA
// ============================================================

// Define a função assíncrona que busca e exibe os produtos em uma tabela HTML
async function listarProdutos() {

  // Seleciona o corpo da tabela (tbody) onde os produtos serão inseridos
  const tbody = document.querySelector("#produtosTable tbody");

  try {
    // Faz uma requisição ao arquivo PHP que retorna a lista de produtos
    const r = await fetch("../PHP/cadastro_produtos.php?listar_produtos=1");

    // Verifica se a resposta da requisição foi bem-sucedida
    if (!r.ok) throw new Error("Falha ao listar produtos!");

    // Insere diretamente o HTML retornado pelo PHP dentro do corpo da tabela
    tbody.innerHTML = await r.text();

    // ============================================================
    // Adiciona eventos de clique aos botões "Editar" de cada produto
    // ============================================================

    // Seleciona todos os botões com a classe ".btn-editar-produto"
    tbody.querySelectorAll(".btn-editar-produto").forEach(btn => {

      // Para cada botão encontrado, adiciona um evento de clique
      btn.addEventListener("click", (e) => {

        // Impede o comportamento padrão do botão (ex: envio de formulário)
        e.preventDefault();

        // Obtém o ID do produto a partir do atributo data-id do botão
        const produtoId = btn.dataset.id;

        // Chama a função que carrega os dados do produto no formulário de edição
        carregarProduto(produtoId);
      });
    });

  } catch (e) {
    // Caso ocorra erro em qualquer parte do processo (ex: PHP fora do ar)
    // Exibe uma linha na tabela informando o erro
    tbody.innerHTML = '<tr><td colspan="12" class="text-center">Erro ao carregar produtos</td></tr>';

    // Mostra o erro também no console do navegador (para debug)
    console.error(e);
  }
}



// ============================================================
// 2️⃣ FUNÇÃO PARA CARREGAR OS DADOS DO PRODUTO NO FORMULÁRIO
// ============================================================

// Define uma função assíncrona que busca os dados de um produto pelo ID
async function carregarProduto(produtoId) {
  try {
    // Faz a requisição ao PHP passando o ID do produto
    const r = await fetch(`../PHP/cadastro_produtos.php?buscar_produto&id=${produtoId}`);

    // Se o PHP retornar erro (status diferente de 200), lança exceção
    if (!r.ok) throw new Error("Erro ao buscar produto!");

    // Converte o retorno para JSON (espera-se que o PHP retorne dados do produto)
    const data = await r.json();

    // Se não houver produto com esse ID, sai da função
    if (!data || !data.idProdutos) return;

    // ============================================================
    // Função auxiliar interna: define o valor de um campo do formulário
    // ============================================================
    const setValue = (selector, value) => {
      const el = document.querySelector(selector); // Seleciona o elemento pelo seletor
      if (el) el.value = value ?? ""; // Se existir, define o valor (ou vazio se for nulo)
    };

    // ============================================================
    // Preenche os campos do formulário com os dados retornados
    // ============================================================
    setValue("#idproduto", data.idProdutos);
    setValue("#pNome", data.nome);
    setValue("#pDescricao", data.descricao);
    setValue("#pQtd", data.quantidade ?? 0);
    setValue("#pPreco", data.preco ?? 0);
    setValue("#pPrecoPromo", data.preco_promocional ?? 0);
    setValue("#pTamanho", data.tamanho);
    setValue("#pCor", data.cor);
    setValue("#pCodigo", data.codigo);
    setValue("#pMarca", data.marcas_id ?? 1); // Define a marca, ou 1 por padrão

    // ============================================================
    // PREENCHE O SELECT DE CATEGORIAS DO PRODUTO
    // ============================================================

    // Seleciona o campo <select> das categorias
    const selectCategoria = document.querySelector("#prodCategoria");

    if (selectCategoria) {
      // Remove opção temporária anterior (se houver)
      const tempOpt = selectCategoria.querySelector(".temp-cat");
      if (tempOpt) tempOpt.remove();

      // Se o produto tem categoria associada, garante que ela esteja no select
      if (data.categoria_id && data.categoria_nome) {
        // Procura se a categoria já existe como opção no select
        let option = selectCategoria.querySelector(`option[value='${data.categoria_id}']`);

        // Se não existir, cria uma nova opção temporária com o nome e o ID da categoria
        if (!option) {
          option = document.createElement("option");
          option.value = data.categoria_id;
          option.textContent = data.categoria_nome;
          option.classList.add("temp-cat"); // Marca como temporária (será removida depois)
          selectCategoria.appendChild(option);
        }
      }

      // Define o valor selecionado no select, ou 0 se não houver categoria
      selectCategoria.value = data.categoria_id ?? 0;
    }

    // ============================================================
    // MOSTRAR IMAGENS DO PRODUTO (OU PLACEHOLDERS)
    // ============================================================

    // Array contendo os seletores das imagens e placeholders
    const imgs = [
      { imgEl: "#pImg1Prev", phEl: "#pImg1Ph", dataImg: data.imagem },
      { imgEl: "#pImg2Prev", phEl: "#pImg2Ph", dataImg: data.imagem2 },
      { imgEl: "#pImg3Prev", phEl: "#pImg3Ph", dataImg: data.imagem3 },
    ];

    // Percorre cada imagem configurada
    imgs.forEach(({ imgEl, phEl, dataImg }) => {
      // Seleciona o elemento de imagem e o placeholder
      const imgElement = document.querySelector(imgEl);
      const placeholder = document.querySelector(phEl);

      // Se o elemento de imagem não existir, pula
      if (!imgElement) return;

      // Se houver imagem no banco de dados, exibe ela
      if (dataImg) {
        imgElement.src = `data:image/jpeg;base64,${dataImg}`; // Converte Base64 em imagem
        imgElement.classList.remove("d-none"); // Mostra imagem
        placeholder?.classList.add("d-none"); // Esconde placeholder
      } 
      // Caso contrário, mostra apenas o placeholder
      else {
        imgElement.src = "";
        imgElement.classList.add("d-none");
        placeholder?.classList.remove("d-none");
      }
    });

    // ============================================================
    // FAZ SCROLL AUTOMÁTICO ATÉ O FORMULÁRIO DE PRODUTO
    // ============================================================
    const formEl = document.querySelector("#formProduto");
    if (formEl) formEl.scrollIntoView({ behavior: "smooth" }); // Rola suavemente até o formulário

  } catch (err) {
    // Caso ocorra erro (ex: falha na conexão com PHP)
    console.error("Erro ao carregar produto:", err);
  }
}



// ============================================================
// 3️⃣ INICIALIZA A LISTAGEM AUTOMÁTICA AO CARREGAR A PÁGINA
// ============================================================

// Quando o documento terminar de carregar (DOM completo)
document.addEventListener("DOMContentLoaded", () => {

  // Chama a função listarProdutos para preencher a tabela automaticamente
  listarProdutos();
});
