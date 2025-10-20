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

  // ================= Função para listar categorias em <select> =================
  async function listarcategorias(nomeid) {
    const sel = document.querySelector(nomeid);
    if (!sel) return;

    try {
      const r = await fetch("../PHP/cadastro_categorias.php?listar=1");
      if (!r.ok) throw new Error("Falha ao listar categorias!");
      sel.innerHTML = await r.text();
    } catch (e) {
      sel.innerHTML = "<option disabled>Erro ao carregar</option>";
    }
  }

  // ================= Listagem de banners =================
  function listarBanners(tbbanner) {
    const tbody = document.getElementById(tbbanner);
    if (!tbody) return;
    const url = "../PHP/banners.php?listar=1";

    const esc = s => (s || "").replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    const ph = () => 'data:image/svg+xml;base64,' + btoa(`
      <svg xmlns="http://www.w3.org/2000/svg" width="96" height="64">
        <rect width="100%" height="100%" fill="#eee"/>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
              font-family="sans-serif" font-size="12" fill="#999">SEM IMAGEM</text>
      </svg>
    `);
    const dtbr = iso => {
      if (!iso) return "-";
      const [y,m,d] = String(iso).split("-");
      return y && m && d ? `${d}/${m}/${y}` : "-";
    };
    const row = b => {
      const src = b.imagem ? `data:image/jpeg;base64,${b.imagem}` : ph();
      const cat = b.categoria_nome || "-";
      const link = b.link ? `<a href="${esc(b.link)}" target="_blank" rel="noopener">abrir</a>` : "-";
      return `
        <tr>
          <td><img src="${src}" alt="banner" style="width:96px;height:64px;object-fit:cover;border-radius:6px"></td>
          <td>${esc(b.descricao || "-")}</td>
          <td class="text-nowrap">${dtbr(b.data_validade)}</td>
          <td>${esc(cat)}</td>
          <td>${link}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-warning" data-id="${b.id}">Editar</button>
            <button class="btn btn-sm btn-danger"  data-id="${b.id}">Excluir</button>
          </td>
        </tr>
      `;
    };

    fetch(url, { cache: "no-store" })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || "Erro ao listar banners");
        const arr = d.banners || [];
        tbody.innerHTML = arr.length
          ? arr.map(row).join("")
          : `<tr><td colspan="6" class="text-center text-muted">Nenhum banner cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  }

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
  listarCupons("tabelaCupons");
  listarcategorias("#categoriaBanner");
  listarcategorias("#categoriasPromocoes");
});
