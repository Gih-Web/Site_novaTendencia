document.addEventListener("DOMContentLoaded", () => {
  // ================= Pré-visualização de imagem =================
  const inputFoto = document.querySelector('input[name="foto"]');
  const previewBox = document.querySelector(".banner-thumb");
  if (inputFoto && previewBox) {
    inputFoto.addEventListener("change", () => {
      const file = inputFoto.files && inputFoto.files[0];
      if (!file) {
        previewBox.innerHTML = '<span class="text-muted">Prévia</span>';
        return;
      }
      if (!file.type.startsWith("image/")) {
        previewBox.innerHTML = '<span class="text-danger small">Arquivo inválido</span>';
        inputFoto.value = "";
        return;
      }
      const reader = new FileReader();
      reader.onload = e => {
        previewBox.innerHTML = `<img src="${e.target.result}" alt="Prévia do banner">`;
      };
      reader.readAsDataURL(file);
    });
  }
});
  // ================= Função para listar categorias em <select> =================
function listarcategorias(nomeid) {
  // Função assíncrona autoexecutável (IIFE) para permitir uso de await
  (async () => {
    // Seleciona o elemento HTML informado no parâmetro (ex: um <select>)
    const sel = document.querySelector(nomeid);

    try {
      // Faz a requisição ao PHP que retorna a lista de categorias
      const r = await fetch("../PHP/cadastro_categorias.php?listar=1");

      // Se o retorno do servidor for inválido (status diferente de 200), lança erro
      if (!r.ok) throw new Error("Falha ao listar categorias!");

      /*
        Se os dados vierem corretamente, o conteúdo retornado pelo PHP 
        (geralmente <option>...</option>) é inserido dentro do elemento HTML.
        innerHTML é usado para injetar esse conteúdo diretamente no campo.
      */
      sel.innerHTML = await r.text();
    } catch (e) {
      // Caso haja erro (rede, servidor, etc.), exibe uma mensagem dentro do select
      sel.innerHTML = "<option disable>Erro ao carregar</option>";
    }
  })();
}



 // ================= Lista banners e habilita "Editar" =================
function listarBanners(tbbanner) {
  const tbody = document.getElementById(tbbanner);
  if (!tbody) return;

  const url = '../PHP/banners.php?listar=1';
  let byId = new Map();

  const esc = s => (s || '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

  const ph = () => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="96" height="64">
       <rect width="100%" height="100%" fill="#eee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             font-family="sans-serif" font-size="12" fill="#999">SEM IMAGEM</text>
     </svg>`
  );

  const dtbr = iso => {
    if (!iso) return '-';
    const [y,m,d] = String(iso).split('-');
    return (y && m && d) ? `${d}/${m}/${y}` : '-';
  };

  const row = b => {
    const src  = b.imagem ? `data:image/*;base64,${b.imagem}` : ph();
    const cat  = b.categoria_nome || '-';
    const link = b.link ? `<a href="${esc(b.link)}" target="_blank" rel="noopener">abrir</a>` : '-';

    byId.set(String(b.id), b);

    return `
      <tr>
        <td><img src="${src}" alt="banner" style="width:96px;height:64px;object-fit:cover;border-radius:6px"></td>
        <td>${esc(b.descricao || '-')}</td>
        <td class="text-nowrap">${dtbr(b.data_validade)}</td>
        <td>${esc(cat)}</td>
        <td>${link}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning btn-edit" data-id="${b.id}">Selecionar</button>
        </td>
      </tr>`;
  };

  const carregar = () => {
    fetch(url, { cache: 'no-store' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || 'Erro ao listar banners');
        const arr = d.banners || [];
        byId = new Map();
        tbody.innerHTML = arr.length
          ? arr.map(row).join('')
          : `<tr><td colspan="6" class="text-center text-muted">Nenhum banner cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  };

  carregar();

  tbody.addEventListener('click', (ev) => {
    const btn = ev.target.closest('button');
    if (!btn) return;

    if (btn.classList.contains('btn-edit')) {
      const id = btn.getAttribute('data-id');
      const banner = byId.get(String(id));
      if (!banner) return alert('Não foi possível localizar os dados deste banner.');
      preencherFormBanner(banner);
    }
  });
}
// ================= Helper de pré-visualização =================
function setPreview(src) {
  const previewBox = document.getElementById('previewBanner') || document.querySelector('.banner-thumb');
  if (!previewBox) return;

  const ph = () => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="320" height="160">
       <rect width="100%" height="100%" fill="#f2f2f2"/>
       <text x="50%" y="50%" font-size="14" fill="#999" text-anchor="middle" dominant-baseline="middle">Prévia</text>
     </svg>`
  );

  previewBox.innerHTML = '';
  const img = document.createElement('img');
  img.src = src || ph();
  img.alt = 'Prévia do banner';
  img.className = 'img-fluid';
  img.style.maxHeight = '160px';
  img.style.objectFit  = 'contain';
  previewBox.appendChild(img);
}

// ================= Preencher formulário =================
function preencherFormBanner(banner) {
  const form = document.getElementById('formBanner') || document.querySelector('form');
  const acaoInput = document.getElementById('acao') || form.querySelector('input[name="acao"]');
  const idInput   = document.getElementById('idBanner') || form.querySelector('input[name="id"]');

  form.querySelector('input[name="descricao"]').value = banner.descricao || '';
  form.querySelector('input[name="data"]').value      = banner.data_validade || '';
  form.querySelector('input[name="link"]').value      = banner.link || '';
  const sel = form.querySelector('select[name="categoriab"]');
  if (sel) sel.value = (banner.categoria_id ?? '') + '';

  idInput.value   = banner.id;
  acaoInput.value = 'atualizar';

  const file = form.querySelector('input[name="foto"]');
  if (file) file.value = '';

  setPreview(banner.imagem ? `data:image/*;base64,${banner.imagem}` : null);

  const btnCadastrar = document.getElementById('btnCadastrar');
  if (btnCadastrar) {
    btnCadastrar.textContent = 'Salvar alterações';
    btnCadastrar.classList.remove('btn-primary');
    btnCadastrar.classList.add('btn-success');
  }

  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ================= Botão Editar =================
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formBanner') || document.querySelector('form');
  const btnEditar = document.getElementById('btnEditar');
  const acaoInput = document.getElementById('acao') || form.querySelector('input[name="acao"]');
  const idInput   = document.getElementById('idBanner') || form.querySelector('input[name="id"]');
  if (!form || !btnEditar) return;

  btnEditar.addEventListener('click', () => {
    if (!idInput.value) return alert('Clique em "Editar" na linha da tabela para carregar um banner primeiro.');
    acaoInput.value = 'atualizar';
    form.submit();
  });
});

// ================= Botão Excluir =================
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formBanner') || document.querySelector('form');
  const btnExcluir = document.getElementById('btnExcluir');
  const idInput = document.getElementById('idBanner') || form.querySelector('input[name="id"]');
  const previewBox = document.getElementById('previewBanner') || document.querySelector('.banner-thumb');
  const btnCadastrar = document.getElementById('btnCadastrar');
  const acaoInput = document.getElementById('acao') || form.querySelector('input[name="acao"]');
  if (!form || !btnExcluir) return;

  btnExcluir.addEventListener('click', async () => {
    const id = idInput.value;
    if (!id) return alert('Selecione um banner na tabela para excluir.');
    if (!confirm('Tem certeza que deseja excluir este banner?')) return;

    try {
      const fd = new FormData();
      fd.append('acao', 'excluir');
      fd.append('id', id);

      const r = await fetch('../PHP/banners.php', { method: 'POST', body: fd });
      if (!r.ok) throw new Error('Falha na exclusão.');

      alert('Banner excluído com sucesso!');
      form.reset();
      idInput.value = '';
      acaoInput.value = '';
      if (previewBox) previewBox.innerHTML = '<span class="text-muted">Prévia</span>';

      if (btnCadastrar) {
        btnCadastrar.textContent = 'Cadastrar';
        btnCadastrar.classList.remove('btn-success');
        btnCadastrar.classList.add('btn-primary');
      }

      listarBanners('tbBanners');
    } catch (e) {
      alert('Erro ao excluir: ' + (e.message || e));
    }
  });
});





  // ================= Listagem de cupons =================
  function listarCupons(tbcupom) {
    const tbody = document.getElementById(tbcupom);
    if (!tbody) return;
    const url = "../PHP/cupom.php?listar=1";

    const esc = s => (s || "").replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    const dtbr = iso => {
      if (!iso) return "-";
      const [y,m,d] = String(iso).split("-");
      return y && m && d ? `${d}/${m}/${y}` : "-";
    };
    const row = c => `
      <tr>
        <td>${c.id}</td>
        <td>${esc(c.nome)}</td>
        <td>R$ ${parseFloat(c.valor).toFixed(2).replace(".", ",")}</td>
        <td>${dtbr(c.data_validade)}</td>
        <td>${c.quantidade}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${c.id}">Editar</button>
          <button class="btn btn-sm btn-danger"  data-id="${c.id}">Excluir</button>
        </td>
      </tr>
    `;

    fetch(url, { cache: "no-store" })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || "Erro ao listar cupons");
        const arr = d.cupons || [];
        tbody.innerHTML = arr.length
          ? arr.map(row).join("")
          : `<tr><td colspan="6" class="text-center text-muted">Nenhum cupom cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  }



  // ================= Chamadas =================

  listarBanners("tbBanners");
  listarcategorias("#categoriaBanner");

  listarCupons("tabelaCupons");
  listarcategorias("categoriasPromocoes");

