// Função para listar produtos
async function listarProdutos() {
  const tabela = document.getElementById('tabelaProdutos');
  tabela.innerHTML = ''; // limpa a tabela antes de preencher

  try {
    const response = await fetch('cadatro_produtos.php?listarProdutos=1'); // coloque o caminho do seu PHP
    const data = await response.json();

    if (!data.ok) {
      tabela.innerHTML = `<tr><td colspan="12" class="text-center text-danger">Erro ao carregar produtos: ${data.error}</td></tr>`;
      return;
    }

    if (data.count === 0) {
      tabela.innerHTML = `<tr><td colspan="12" class="text-center">Nenhum produto encontrado</td></tr>`;
      return;
    }

    data.produtos.forEach(produto => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td>${produto.imagem ? `<img src="data:image/jpeg;base64,${produto.imagem}" alt="${produto.nome}" width="50">` : 'Sem imagem'}</td>
        <td>${produto.nome}</td>
        <td>${produto.descricao}</td>
        <td>${produto.quantidade}</td>
        <td>R$ ${produto.preco.toFixed(2)}</td>
        <td>${produto.preco_promocional ? 'R$ ' + produto.preco_promocional.toFixed(2) : '-'}</td>
        <td>${produto.tamanho || '-'}</td>
        <td>${produto.cor || '-'}</td>
        <td>${produto.codigo}</td>
        <td>${produto.marca || '-'}</td>
        <td>${produto.categoria || 'Sem categoria'}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-primary" onclick="editarProduto(${produto.idProdutos})">Editar</button>
          <button class="btn btn-sm btn-danger" onclick="excluirProduto(${produto.idProdutos})">Excluir</button>
        </td>
      `;

      tabela.appendChild(row);
    });

  } catch (error) {
    tabela.innerHTML = `<tr><td colspan="12" class="text-center text-danger">Erro ao listar produtos: ${error.message}</td></tr>`;
  }
}

// Chamando a função ao carregar a página
document.addEventListener('DOMContentLoaded', listarProdutos);

// Funções placeholders para ações de editar e excluir
function editarProduto(id) {
  alert(`Editar produto ID: ${id}`);
}

function excluirProduto(id) {
  if (confirm('Deseja realmente excluir este produto?')) {
    alert(`Produto ID ${id} excluído (implementar lógica no PHP)`);
  }
}
