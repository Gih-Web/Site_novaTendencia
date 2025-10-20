// produtos_logista.js

// ================================
// 1) Função para listar produtos
// ================================
async function listarProdutos() {
  try {
    // Faz requisição ao PHP que retorna JSON com os produtos
    const response = await fetch("../PHP/listar_produtos.php");
    if (!response.ok) throw new Error("Falha ao listar produtos");

    const produtos = await response.json();

    // Seleciona o tbody da tabela
    const tbody = document.querySelector("#produtosTable tbody");
    tbody.innerHTML = ""; // Limpa tabela antes de preencher

    // Percorre os produtos e monta as linhas da tabela
    produtos.forEach(prod => {
      const tr = document.createElement("tr");

      // Coluna da imagem (primeira imagem do produto)
      const imgTd = document.createElement("td");
      const imgEl = document.createElement("img");
      imgEl.src = prod.imagem ? `data:image/jpeg;base64,${prod.imagem}` : "../IMG/placeholder.png";
      imgEl.alt = prod.nome;
      imgEl.style.width = "50px";
      imgEl.style.height = "50px";
      imgTd.appendChild(imgEl);
      tr.appendChild(imgTd);

      // Colunas de texto simples
      const colunas = [
        prod.nome,
        prod.descricao,
        prod.quantidade,
        `R$ ${parseFloat(prod.preco).toFixed(2)}`,
        prod.preco_promocional ? `R$ ${parseFloat(prod.preco_promocional).toFixed(2)}` : "-",
        prod.tamanho,
        prod.cor,
        prod.codigo,
        prod.marca,
        prod.categoria
      ];

      colunas.forEach(valor => {
        const td = document.createElement("td");
        td.textContent = valor;
        tr.appendChild(td);
      });

      // Coluna de ações
      const actionsTd = document.createElement("td");
      actionsTd.classList.add("text-end");

      // Botão editar
      const btnEditar = document.createElement("button");
      btnEditar.classList.add("btn", "btn-sm", "btn-outline-secondary", "me-1");
      btnEditar.textContent = "Editar";
      btnEditar.onclick = () => {
        // Aqui você pode chamar a função de edição
        alert("Função de editar não implementada ainda");
      };

      // Botão excluir
      const btnExcluir = document.createElement("button");
      btnExcluir.classList.add("btn", "btn-sm", "btn-outline-danger");
      btnExcluir.textContent = "Excluir";
      btnExcluir.onclick = () => {
        excluirProduto(prod.idProdutos);
      };

      actionsTd.appendChild(btnEditar);
      actionsTd.appendChild(btnExcluir);
      tr.appendChild(actionsTd);

      tbody.appendChild(tr);
    });
  } catch (error) {
    console.error("Erro ao listar produtos:", error);
  }
}

// ================================
// 2) Função para excluir produto
// ================================
async function excluirProduto(id) {
  if (!confirm("Deseja realmente excluir este produto?")) return;

  try {
    const formData = new FormData();
    formData.append("idproduto", id);

    const response = await fetch("../PHP/excluir_produto.php", {
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.sucesso) {
      alert("Produto excluído com sucesso!");
      listarProdutos(); // Atualiza tabela
    } else {
      alert("Erro ao excluir: " + result.erro);
    }
  } catch (error) {
    console.error("Erro ao excluir produto:", error);
  }
}

// ================================
// 3) Inicialização
// ================================
document.addEventListener("DOMContentLoaded", () => {
  listarProdutos(); // Lista produtos ao carregar a página
});
